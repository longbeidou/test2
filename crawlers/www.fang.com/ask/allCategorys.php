<?php

// var_dump(__DIR__);
require __DIR__.'/../../../vendor/autoload.php';

use Hft\Crawler\Libraries\www_fang_com\CategoryAskCrawler;
use Hft\Crawler\Libraries\BaseCrawler;
use Carbon\Carbon;

define('HOST_NAME', 'http://www.fang.com');

$config = [
	'base_uri' => 'http://www.fang.com/',
	'timeout' => 4.0
];

try {
	$crawler = new CategoryAskCrawler($config);
	$info = $crawler->getAllCategoryLinks();
} catch (Exception $e) {
	die('爬取信息失败！');
}

$dbType='mysql';//数据库类型
$host='localhost';//主机名
$dbName='crawler';//数据库名
$userName='root';//用户名
$passWord='root';//密码
//创建link源 数据库类型:主机名;数据库名
$dsn="{$dbType}:host={$host};dbname={$dbName}";

try {
	//创建PDO对象
	$pdo=new PDO($dsn,$userName,$passWord);
} catch (Exception $e) {
	die("mysql connect failed!\n info:".$e->getMessage());	
}

try{
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "INSERT INTO ask_cate VALUE (null, :name, :url, :host, null, :created_at, :updated_at)";
	$list = $pdo->prepare($sql);
	$list->bindParam(':name', $name);
	$list->bindParam(':url', $url);
	$list->bindParam(':host', $host);
	$list->bindParam(':created_at', $created_at);
	$list->bindParam(':updated_at', $updated_at);

	foreach ($info['both'] as $i => $infoBoth) {
		$time = Carbon::now();
		$name = $info['text'][$i];
		$url = $info['url'][$i];
		$host = HOST_NAME;
		$status = 3;
		$created_at = $time;
		$updated_at = $time;
		$list->execute();
	}
}catch(PDOExcetption $e){
    die('操作失败'.$e->getMessage());
}
$pdo = null;
dd(count($info['url']));