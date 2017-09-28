<?php
/*	Name : MyDataSpider System;
	FOR : www.yaozhibiao.com & into yaozhibao.com Mysql;
*/
namespace Rds;

//一些常用的定义
define('Apps', 'app');
define('ConfigFile', '_Config.php');
define('DS', '\\');

class Mds
{
		// $con = file_get_contents('https://www.baidu.com');
		// echo $con;
	//start
	private $config = null;
	function __construct()
	{
		$config = include(Apps . DS . ConfigFile);
	}

	public function Main($argv = "", $argc = "")
	{
		//主方法
		if($argc !== 1){

		} else {
			echo "Usage command \"chcp 65001\" Change for Chinese \n";
			exit;
		}

		return;
	}

	public function GetDatas()
	{
		//获取数据
		return;
	}

	public function Validata()
	{
		//验证数据
		return;
	}

	public function TransToMysql()
	{
		//传送数据
		//	传送数据至数据库
		return;
	}

	public function ShowNames()
	{
		echo "********************************\n";
		echo "**********MyDataSpider**********\n";
		echo "********************************\n";
		return;
	}

}

$mds = new Rds();
$mds->ShowNames();
$mds->Main($argv, $argc);
?>