<?php
/*

	7 = >默认值
	0 = >黑色
	1 =蓝
	2 = >绿色
	3 = >水
	4 = >红色
	5 = >紫色
	6 = >黄
	7 = >浅灰色
	8 = >灰色
	9 = >淡蓝色
	10 = >浅绿色
	11 = >淡水
	12 = >淡红色
	13 = >浅紫色
	14 = >淡黄色
	15 = >白


*/

function changeCmdColor($com, $color)
{
	$com = new COM();

	// register needed features
	$com->Register('kernel32.dll', 'GetStdHandle', 'i=h', 'f=s', 'r=l');
	$com->Register('kernel32.dll', 'SetConsoleTextAttribute', 'i=hl', 'f=s', 'r=t');

	$ch = $com->GetStdHandle(-11);
	$com->SetConsoleTextAttribute($ch, $color);
	return;
}


// $com = new COM('DynamicWrapper');

// // register needed features
// $com->Register('kernel32.dll', 'GetStdHandle', 'i=h', 'f=s', 'r=l');
// $com->Register('kernel32.dll', 'SetConsoleTextAttribute', 'i=hl', 'f=s', 'r=t');

// get console handle
// $ch = $com->GetStdHandle(-11);

// //蓝色
// $com->SetConsoleTextAttribute($ch, 1);
// echo 'test text:blue'.PHP_EOL;

// //默认颜色
// $com->SetConsoleTextAttribute($ch, 7);
// echo 'Back to normal color!'.PHP_EOL;

// //绿色
// $com->SetConsoleTextAttribute($ch, 2);
// echo 'this is green color text'.PHP_EOL;

?>