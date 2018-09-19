#!/usr/bin/php
<?php
	switch ($argv[1]) {
		case 'www.fang.com':
?>
www.fang.com
<?php
			break;
		case 'www.lianjia.com':
?>
www.lianjia.com
<?php
			break;
		default:
?>
输入参数错误！
命令使用格式：./crawler 参数1 参数2 参数3 参数4
<?php
var_dump($argc);
			break;
	}
?>
