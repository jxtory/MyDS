<?php
define('DS', '\\');
define('Apps', 'app');
define('ConfigFile', '_Config.php');

function getFile($file)
{
	//加载配置文件的函数
	if(file_exists($file)){
		return include($file);
	} else {
		return null;
	}
}

function truncate($db, $sql)
{
	$res = $db->query($sql);
	if($res){
		echo $sql . PHP_EOL;
		echo "Truncate table ok." . PHP_EOL;
	} else {
		echo "Fail." . PHP_EOL;
	}

	return;
}

$config = getFile(Apps . DS . ConfigFile);
$hip = $config['mysql']['hostip'];
$fd = $config['mysql']['fromdb'];
$un = $config['mysql']['username'];
$pw = $config['mysql']['password'];

$db = new mysqli($hip, $un, $pw, $fd);

if($db && $db->error == ""){
	$s1 = "truncate table myds_urllisttask;";
	$s2 = "truncate table myds_runinfo_log;";
	$s3 = "truncate table myds_spider_datas;";
	$s4 = "truncate table myds_visited_url;";
	truncate($db, $s1);
	truncate($db, $s2);
	truncate($db, $s3);
	truncate($db, $s4);
}



?>