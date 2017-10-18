<?php
return [
	'timezone' => 'Asia/Shanghai',
	'mysql'	=>	[
		'username'	=>	'root',
		'password'	=>	'',
		'hostip'	=>	'127.0.0.1',
		'fromdb'	=>	'myds'
	],
	'mysql_pfname' => 'myds_',
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
	'byhost' => true,
	'byhost_lang' => true,
	'clearOld' => true,
	'lazyMode' => true,

];