<?php
namespace Mds;
use mysqli;
/*	
	Name : MyDataSpider System;
	FOR : www.yaozhibiao.com & into yaozhibao.com Mysql;
*/
// ini_set ('memory_limit', '128M');
//一些常用的定义
define('DS', '\\');
define('Apps', 'app');
define('ConfigFile', '_Config.php');
define('HeadersFile', '_Headers.php');
define('ValidatasFile', '_Validatas.php');
//regex;
define('rxUrl', '/((https|http|ftp|rtsp|mms)?:\/\/)[^\s|\"]+/');
define('rxDepth', '/[1-9]{2}/');
define('rxTitle', '/<title>(.*)<\/title>/');

// 注释区
// define('rxUrl', '/(http|https|ftp):\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\’:+!]*([^<>\”])*/');
//End 注释区
class Mds
{
	//start
	/*
		--file and config
		$app_debug = null 		应用调试
		$config 				配置文件
		$headers 				请求头信息文件
		$validatas 				验证文件
		$mysql 					数据库
		--var
		$curDepth				深度位置
		$prevDepth				上一次深度位置
		$maxDepth				最大深度				$argv[1]
		$seedUrl				种子Url地址				$argv[2]
		$curMode 				当前模式				$argv[3]
		$curUrl 				当前URL
		$nextUrl 				下一次URL
		$pid 					父任务
		--infomation
		$info_StartDate			开始时间
		$info_EndDate			结束时间
		$info_RunDate 			运行时常
		$info_FindUrl			发现的Url数
		$info_RecordTotal		记录总数
		--MysqlDatas
		$table_ult 				任务库
		$table_vu				过滤表
		$table_sd 				数据表
		$table_rl 				运行信息表
	*/
	
	//file area
	protected $app_debug = null;
	protected $config = null;
	protected $headers = null;
	protected $validatas = null;
	protected $mysql = null;

	//var area
	protected $curDepth = null;
	protected $prevDepth = null;
	protected $maxDepth = null;
	protected $seedUrl = null;
	protected $curMode = null;
	protected $curUrl = null;
	protected $nextUrl = null;
	protected $pid = null;
	//infomation
	protected $info_StartDate = null;
	protected $info_EndDate = null;
	protected $info_RunDate = null;
	protected $info_FindUrl = null;
	protected $info_RecordTotal = null;
	//MysqlDatas
	protected $table_ult = null;
	protected $table_vu = null;
	protected $table_sd = null;
	protected $table_rl = null;

	function __construct()
	{
		//调试模式
		$this->app_debug = false;
		//start
		$this->showProject();
		//载入配置文件
		$this->config = getFile(Apps . DS . ConfigFile);
		$this->headers = getFile(Apps . DS . HeadersFile);
		$this->validatas = getFile(Apps . DS . ValidatasFile);

		//初始化数据
		//设置时区
		if(isset($this->config['timezone']) || !empty($this->config['timezone'])){
			date_default_timezone_set($this->config['timezone']);
		} else {
			date_default_timezone_set('Asia/Shanghai');
		}


		if(empty($this->headers)){$this->headers = array("null");}

		if(isset($this->config['mysql_pfname'])){
			$this->table_ult = $this->config['mysql_pfname'] . "urllisttask";
			$this->table_vu = $this->config['mysql_pfname'] . "visited_url";
			$this->table_sd = $this->config['mysql_pfname'] . "spider_datas";
			$this->table_rl = $this->config['mysql_pfname'] . "runinfo_log";
		} else {
			$this->table_ult = "myds_" . "urllisttask";
			$this->table_vu = "myds_" . "visited_url";
			$this->table_sd = "myds_" . "spider_datas";
			$this->table_rl = "myds_" . "runinfo_log";
		}

		if(!$this->opMysqlConnect()){
			er();
			er();
			echo "Error:" . PHP_EOL;
			echo "1.\tMysql connection failed!" . PHP_EOL;
			echo "2.\tPlease check the configuration file." . PHP_EOL;
			exit();
		}
	}

	function __destruct()
	{

		//记录运行信息
		$this->info_EndDate = time();
		$this->info_RunDate = $this->info_EndDate - $this->info_StartDate;
		$res = $this->opMysqladdToRL($this->info_StartDate, $this->info_EndDate, $this->info_RunDate, $this->info_FindUrl, $this->info_RecordTotal);
		if($res){
			echo "Log write successful";
			er();
		}
		//End 记录运行信息

		//卸载数据库
		if(!empty($this->mysql)){
			$this->mysql->close();
			$this->mysql = null;
		}
		//End 卸载数据库
	}

	public function Main($argv = "", $argc = "")
	{
		//主方法
		if($argc >= 3){
			//debug
			if($this->app_debug){var_dump($argv);}
			//end debug

			//set depth 不能小于1 不能超过20
			$this->maxDepth = $argv[1];
			$this->maxDepth = floor($this->maxDepth);
			if($this->maxDepth < 2){$this->maxDepth = 2;}
			if($this->maxDepth > 20){$this->maxDepth = 20;}
			//验证种子url
			if(preg_match(rxUrl, $argv[2]) == 1){
				// echo "The url is all right." . PHP_EOL;
				$this->seedUrl = $argv[2];
			} else {
				echo "The url format is wrong." . PHP_EOL;
				exit();
			}
			//判断是否是 fgc模式//模式有两种, fgc 和 cUrl;
			if(isset($argv[3])){
				$this->curMode = $argv[3];
			} else {
				$this->curMode = "not set";
			}

			$this->Run($this->curMode, $this->seedUrl, $this->headers);
			// $this->Run($this->curMode, $argv[2], $this->headers);
		} else {
			//输出未启动的原因信息
			echo "Usage command \"chcp 65001\" Change for Chinese " . PHP_EOL;
			echo "Run Method:\tphp -f Run.php deepth url mode(fgc|cUrl)" . PHP_EOL;
			exit;
		}

		return;
	}

	public function Run($mode, $url = "", $header = array())
	{
		//Show
		er();
		echo "Now.Ready Run.";
		er();
		echo "MaxDepth:\t" . $this->maxDepth;
		er();
		echo "SeedUrl:\t" . $this->seedUrl;
		er();
		//End Show
		//统计时间
		$this->info_StartDate = time();

		//End 统计时间
		//初始化URL-List任务库
		$this->opMysqltcult();
		$this->curDepth = 1;
		$hsc = checkUrl($this->seedUrl);
		$res = $this->opMysqladdToULT($this->seedUrl, $this->curDepth, 1, $hsc);
		//种子网址不经历过滤库验证的过程
		if($res){echo "seedUrl add success!" . PHP_EOL;}
		//End UrlListTask.
		//根据模式执行任务;
		if(strtolower($mode) == "fgc"){
			echo "fgc mode" . PHP_EOL;
			$su = $this->opMysqlgetUrlFromULT($this->curDepth);
			//释放种子
			$this->seedUrl = null;
			//End 释放种子
			$this->curUrl = $su;
			$this->nextUrl = $su;
			if(preg_match(rxUrl, $su) == 1){
				while($this->nextUrl != null){
					$this->loopMode($this->nextUrl);
				}
				// echo "Done or nextUrl is null. The end.";
				er();
				echo "The URL was detected null. Maybe it's over.";
				er();
				echo "Mission accomplished.Thank You!";
				er();
				exit();

			} else {
				echo "1.\tThe url format is wrong." . PHP_EOL;
				echo "2.\tOr NextUrl is null." . PHP_EOL;
				exit();
			}

		} elseif (strtolower($mode) == "cUrl") {
			echo "cUrl mode";
			echo "[develop]";
			exit();
		} else {
			er();
			er();
			echo "Error:\t" . PHP_EOL;
			echo "Run Method:\tphp -f Run.php deepth url mode(fgc|cUrl) . PHP_EOL";
			exit;
		}

		return;
	}

	static public function GetAppDebug()
	{
		return $this->app_debug;
	}

	private function getDatasFromFGC($url = null)
	{
		if($url == null){return false;}

		$datas = file_get_contents($url);
		return $datas;
	}

	private function getDatasFromcUrl()
	{
		return;
	}

	private function getDatasFromValidated($datas)
	{
		$finishdatas = null;
		//将本次任务Url加入到Visited_url表中；
		$url = $datas[0];
		//本次任务作为父ID
		$this->pid = $url[5];
		//查询过滤表 是否存在 相应URl. 如果不存在，则加入新的过滤信息
		$sql = "select url from {$this->table_vu} where url = \"{$url[0]}\"";
		$res = $this->opMysqlQuery($sql);
		if($res){
			if($res->num_rows == 0){
				$res2 = $this->opMysqladdToVU($url[0], $url[2], $url[1], $url[3] ,$url[4], $url[5]);
				if($res2){
					echo "Data acquisition complete. The url add to \"Visited_Url\"";
					er();
				} else {
					echo "The url add to \"Visited_Url\" Fail.";
					er();
					// exit();
				}
			}
		}

		$res->free();
		//End将本次任务Url加入到Visited_url表中；

		//从任务库中删除本次的任务url
		$sql = "delete from {$this->table_ult} where url = \"{$this->curUrl}\"";
		$res = $this->opMysqlQuery($sql);
		if($res){
			//debug
			if($this->app_debug){
				echo "Delete current url from table:{$this->table_ult}.";
				er();
			}
			//end debug
		}
		//End从任务库中删除本次的任务url

		//新添加URL-List
		$urls = $datas[1];
		$pid = $url[5];
		if(is_array($urls) || count($urls) > 0){
			foreach ($urls as $key => $value) {
				$sql = "select url from {$this->table_vu} where url = \"$value\"";
				$res = $this->opMysqlQuery($sql);
				$sql = "select url from {$this->table_ult} where url = \"$value\"";
				$res2 = $this->opMysqlQuery($sql);
				if($res && $res2){
					if($res->num_rows == 0 && $res2->num_rows == 0){
						//当前深度不得等于最大深度，因为下一个深度不能超过 最大深度 maxDepth
						if($this->curDepth != $this->maxDepth){
							$this->opMysqladdToULT($value, $this->curDepth + 1, $pid, checkUrl($value, false));
						}
					}
					// mysqli_free_result($res);
					// mysqli_free_result($res2);
					$res->free();
					$res2->free();
				} else {
					echo "Error:\tScrap an URL.";
					er();
				}
			}
		}
		//End新添加URL-List

		//返回提取的新数据
		$validataMode = null;
		if(!isset($this->validatas['mode']) || empty($validataMode = $this->validatas['mode'])){
			//默认模式为 regex;
			$validataMode = "regex";
		} else {
			$validataMode = $this->validatas['mode'];
		}

		//临时变量
		$temp = null;

		//出去验证配置
		//	regex 模式
		$regexs = $this->validatas['regex'];
		$regex = null;
		//	DOM模式
		$DOMs = $this->validatas['DOM'];
		$DOM = null;

		if($validataMode = "regex"){
			foreach ($regexs as $key => $value) {
				$regex[] = $value;
			}

			$datacount = count($datas);

			if($datacount < 2){
				$finishdatas = "pass";
				return $finishdatas;
			} else {
				$j = 0;
				for ($i=2; $i < $datacount; $i++) { 
					$temp = null;
					$temp[] = $regex[$j];
					$temp[] = $datas[$i];
					$j++;
					// foreach ($datas[$i] as $key => $value) {
					// 	echo $i . "|" . $key . "=>" . $value;
					// 	er(); 
					// }
					$finishdatas[] = $temp;
				}
			}
		}

		// echo "Done!Enter next url for loop";
		echo "Get Done!\tBegin in transfer datas to MySQL.";
		er();

		return $finishdatas;
	}

	private function loopMode($url = null)
	{
		echo "A new round begins.";
		er();
		echo "Current Depth:\t" . $this->curDepth;
		er();
		$this->curUrl = $url;
		echo "Current Url:\t" . $this->curUrl;
		er();
		if($this->curMode == "fgc"){
			if($url == null){return false;}
			$contents = $this->getDatasFromFGC($url);
			$datas = $this->validata($contents);
			$datas = $this->getDatasFromValidated($datas);
			$res = $this->transDataToMysql($datas);

			//准备下一次的循环数据
			if($res || $res == "pass"){
				//记录上一次的深度
				$this->prevDepth = $this->curDepth;
				//深度递增
				if($this->curDepth == ($this->maxDepth - 1)){
					$this->curDepth = 2;
				} else {
					$this->curDepth++;
				}
				//从任务库中 提取新URL;
				$this->nextUrl = $this->opMysqlgetUrlFromULT($this->curDepth);

			}
			return true;
		}

		if($this->curMode == "cUrl"){
			return;
		}

		return false;
	}

	private function opMysqlConnect()
	{
		//读取config['mysql']
		$uname = $this->config['mysql']['username'];
		$pword = $this->config['mysql']['password'];
		$hip = $this->config['mysql']['hostip'];
		$fdb = $this->config['mysql']['fromdb'];
		//建立连接
		$this->mysql = @new mysqli($hip, $uname, $pword, $fdb);
		if($this->mysql->connect_errno){
			echo "MySql Connnect Error:" . $this->mysql->connect_error;
			return false;
		} else {
			$this->mysql->set_charset("utf8");
			return true;
		}
	}

	private function opMysqlQuery($sql)
	{
		$result = $this->mysql->query($sql);
		return $result;
	}

	private function opMysqladdToRL($sd = 0, $ed = 0, $rd = 0, $fu = 0, $rT = 0)
	{
		$sql = "insert into {$this->table_rl}(startDate, endDate, runDate, find_url, recordTotal)";
		$sql .= "values(?, ?, ?, ?, ?)";
		$mstmt = $this->opMysqlPrepare($sql);
		$mstmt->bind_param("iiiii", $sd, $ed, $rd, $fu, $rT);
		$rb = $mstmt->execute();
		$mstmt->free_result();
		$mstmt->close();
		if($rb){
			return true;
		} else {
			return false;
		}
	}

	private function opMysqladdToSD($cs = "", $rod = "", $fu = "", $oud = 0)
	{
		//判断内容重复
		$sql = "select contents from {$this->table_sd} where contents = \"{$cs}\" and from_url = \"{$fu}\"";
		$res = $this->opMysqlQuery($sql);
		if($res){
			if($res->num_rows > 0){
				return false;
			}
		}
		//添加内容到数据表
		$sql = "insert into {$this->table_sd}(contents, ruleordom, from_url, on_Url_depth) ";
		// $sql .= "values(\"{$cs}\", \"{$rod}\", \"{$fu}\", {$oud})";
		$sql .= "values(?, ?, ?, ?)";
		// $res = $this->opMysqlQuery($sql);
		$mstmt = $this->opMysqlPrepare($sql);
		$mstmt->bind_param("sssi", $cs, $rod, $fu, $oud);
		$rb = $mstmt->execute();
		$mstmt->free_result();
		$mstmt->close();

		//返回值
		if($rb){
			//统计找到内容
			$this->info_RecordTotal++;
			// $this->info_RecordTotal += count($datas[$i]);
			//end 统计找到内容

			return true;
		} else {
			return false;
		}

	}

	private function opMysqladdToULT($url, $od, $pid = 0, $hsc = "unknown")
	{
		$eurl = md5($url);
		$sql = "insert into {$this->table_ult}(pid, url, encryption_url, on_depth, http_statuscode) values({$pid}, \"{$url}\", \"{$eurl}\", {$od}, \"{$hsc}\")";
		$res = $this->opMysqlQuery($sql);

		if($res){
			//统计找到网址
			$this->info_FindUrl++;
			// $this->info_FindUrl += count($urls);
			//End 统计找到网址

			return true;
		} else {
			return false;
		}

	}

	private function opMysqladdToVU($url, $od, $title = "unknown", $hsc = "unknown", $fs = "unknown", $oid = 0)
	{
		$eurl = md5($url);
		$sql = "insert into {$this->table_vu}(url, encryption_url, title, on_depth, http_statuscode, filesize, oid) value(\"{$url}\", \"{$eurl}\", \"{$title}\", {$od}, \"{$hsc}\", \"{$fs}\", {$oid})";
		$res = $this->opMysqlQuery($sql);

		if($res){
			return true;
		} else {
			return false;
		}

	}

	private function opMysqlgetUrlFromULT($od)
	{
		//判断种子是否启动
		if($this->seedUrl !== null){
			$sql = "select * from {$this->table_ult} where url = \"{$this->seedUrl}\"";
			$res = $this->opMysqlQuery($sql);
			if($res){
				$row = $res->fetch_assoc();
				return $row['url'];
			} else {
				echo "wrong." . PHP_EOL;
				return false;
			}
			return false;
		} else {
			//开启搜索模式
			// $sql = "select * from {$this->table_ult} where on_depth = {$od} and pid = {$this->pid}";
			$sql = "select * from {$this->table_ult} where on_depth = {$od}";
			$res = $this->opMysqlQuery($sql);
			if($res){
				$row = $res->fetch_assoc();
				if(is_array($row)){
					return $row['url'];
				} else {
					//记录深度
					$this->prevDepth = $this->curDepth;
					//深度递减
					if($this->curDepth > 2){
						$this->curDepth--;
					} else {
						return null;
					}
					//从任务库中 提取新URL;
					return $this->opMysqlgetUrlFromULT($this->curDepth);
				}

			} else {
				//记录深度
				$this->prevDepth = $this->curDepth;
				//深度递减
				if($this->curDepth > 2){
					$this->curDepth--;
				} else {
					return null;
				}
				//从任务库中 提取新URL;
				return $this->opMysqlgetUrlFromULT($this->curDepth);
			}
			
			$res->free();
		}

		return false;
	}

	private function opMysqlPrepare($sql)
	{
		$mstmt = $this->mysql->prepare($sql);
		return $mstmt;
	}

	private function opMysqltcult()
	{
		$result = $this->opMysqlQuery("truncate table {$this->table_ult}");
		return;
	}

	private function showProject()
	{
		echo "********************************" . PHP_EOL;
		echo "**********MyDataSpider**********" . PHP_EOL;
		echo "********************************" . PHP_EOL;
		echo "********** By Jxtory ***********" . PHP_EOL;
		return;
	}

	private function transDataToMysql($datas)
	{
		//传送数据
		//	传送数据至数据库
		if($datas == "pass"){
			if($this->app_debug){
				echo "Discard a data.";
				er();
			}
			return "pass";
		} else {
			$datacount = count($datas);
			//debug
			if($this->app_debug){
				echo "In Function transDataToMysql.\tCount: \t{$datacount}.";
				er();
			}
			//end debug

			for ($i=0; $i < $datacount; $i++) { 
				//debug
				if($this->app_debug){
					echo "checked:\tdatacount.";
					er($datacount);
					er();
				}
				//end debug
				foreach ($datas[$i][1] as $key => $value) {
					//debug
					if($this->app_debug){
						er();
						echo "Value:\t\"{$value}\" into Mysql Table.";
						er();
					}
					//end debug
					$res = $this->opMysqladdToSD($value, $datas[$i][0], $this->curUrl, $this->curDepth);
					//debug
					if($this->app_debug){
						if($res){
							echo "One data added successfully";
							er();
						} else {
							echo "One data added failed";
							er();
						}
					}
					//end debug
				}
			}
			return true;
		}

	}

	private function validata($contents)
	{
		//验证数据
		//	取出正则配置
		$mode = null;
		//	regex 模式
		$regexs = $this->validatas['regex'];
		$rxopt = $this->validatas['rxopt'];
		$regex = null;
		//	DOM模式
		$DOMs = $this->validatas['DOM'];
		$Domopt = $this->validatas['Domopt'];
		$DOM = null;


		$datas = null;
		$data = null;
		$temp = null;
		$tovu = null;

		//urlToVu
		// get_headers(url)
		$tovu[] = $this->curUrl;
		$tovu[] = preg_match(rxTitle, $contents, $temp) ? $temp[1] : false;
		$tovu[] = $this->curDepth;
		$tovu[] = checkUrl($this->curUrl, false);
		$tovu[] = strlen($contents);
		$sql = "select id from {$this->table_ult} where url = \"{$this->curUrl}\"";
		$res = $this->opMysqlQuery($sql)->fetch_assoc();
		$tovu[] = $res['id'];
		//end urlToVu

		$datas[] = $tovu;

		if(!isset($this->validatas['mode']) || empty($mode = $this->validatas['mode'])){
			//默认模式为 regex;
			$mode = "regex";
		} else {
			$mode = $this->validatas['mode'];
		}

		if($mode == "regex"){
			if(empty($regexs) || !isset($regexs)){
				er();er();
				echo "Error:" . PHP_EOL;
				echo "1.\tValidatasFile is null?." . PHP_EOL;
				echo "2.\tPlease check the configuration file." . PHP_EOL;
				echo "3.\tThere is at least one rule for regular expressions." . PHP_EOL;
				exit();
				return false;
			}

			preg_match_all(rxUrl, $contents, $temp);

			foreach ($temp[0] as $key => $value) {
				$res = checkUrl($value);
				if($res != 0 && $res > 199 && $res < 300)	{
					$value = str_replace('"', "", $value);
					$data[] = $value;
				}
			}

			$datas[] = $data;

			foreach ($regexs as $key => $value) {
				$regex[] = $value;
			}

			foreach($regex as $key => $value){
				preg_match_all($value, $contents, $data);
				$datas[] = $data[$rxopt[$key]];
			}


			return $datas;

		}

		if($mode == "DOM"){
			echo "develop";
			er();
		}

	}

}

function er($str = "", $str2 = "")
{
	//输出一个换行符号
	echo $str;
	echo PHP_EOL;
	echo $str2;
}

function getFile($file)
{
	//加载配置文件的函数
	if(file_exists($file)){
		return include($file);
	} else {
		return null;
	}
}

function checkUrl($url, $ad = true)
{
	//返回url状态码
	$ch = curl_init($url);
	$timeout = 10;

	// curl_setopt($ch, CURLOPT_HTTPHEADER  , $header));
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_REFERER, $url);
	curl_setopt($ch, CURLOPT_USERAGENT   , 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36');
	curl_setopt($ch, CURLOPT_ENCODING  , "gzip, deflate, sdch");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //?
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

	// debug
	if($ad == true){
		er();
		echo "Validata this url:\t" . $url;
		er();
	}
	// end debug


	$datas = curl_exec($ch);

	if(!$datas)
	{
		if($ad == true){
			er();
			echo "\tCheckUrl Error:\t" . curl_error($ch);
			er();
		}

		return curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// return false;
	} else {
		return curl_getinfo($ch, CURLINFO_HTTP_CODE); 
	}

	curl_close($ch);

}

?>