<?php
return [
	//时区设置
	'timezone' => 'Asia/Shanghai',
	//数据库连接信息
	'mysql'	=>	[
		'username'	=>	'root',
		'password'	=>	'',
		'hostip'	=>	'127.0.0.1',
		'fromdb'	=>	'myds'
	],
	//数据库前缀名称
	'mysql_pfname' => 'myds_',
	//过滤网址信息
	'urlexc' => [
		'.css',
		'.js',
		'.dtd',
		'.ttf',
		'.jpg',
		'.gif',
		'.png',
		'www.w3.org',
		'www.hao123.com'
	],
	//客户端字符集设置
	'ClientCharacter' => 'utf8',
	//子查询是否仅根据种子的主机名(同域名下查询)
	'byhost' => true,
	//长主机名模式
	'byhost_lang' => false,
	//启动时是否清空旧数据
	'clearOld' => true,
	//是否为懒惰模式
	'lazyMode' => true,

];