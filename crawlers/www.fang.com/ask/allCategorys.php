<?php

// var_dump(__DIR__);
require __DIR__.'/../../../vendor/autoload.php';

use Hft\Crawler\Libraries\www_fang_com\CategoryAskCrawler;
use Hft\Crawler\Libraries\BaseCrawler;
use Carbon\Carbon;

try {
	$config = [
		'base_uri' => 'http://www.fang.com/',
		'timeout' => 4.0
	];

	$crawler = new CategoryAskCrawler($config);
	$info = $crawler->getAllCategoryLinks();
} catch (Exception $e) {
	die('爬取信息失败！');
}

$dbType='mysql';//数据库类型
$host='localhost';//主机名
$dbName='crawler';//数据库名
$userName='crawler';//用户名
$passWord='root4';//密码
//创建link源 数据库类型:主机名;数据库名
$dsn="{$dbType}:host={$host};dbname={$dbName}";

try {
	//创建PDO对象
	$pdo=new PDO($dsn,$userName,$passWord);
} catch (Exception $e) {
	die("mysql connect failed!\n info:".$e->getMessage());	
}


try{
	$sql = "INSERT INTO `ask_cate`('name', 'url', 'host', 'created_at', 'updated_at') VALUES ()";
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $pdo->prepare("INSERT INTO `ask_cate`('name', 'url', 'host', 'created_at', 'updated_at') VALUES (:name, :url, :host, :created_at, :updated_at)");

	foreach ($info as $name => $url) {
		$time = Carbon::now();
		$stmt->bindParam(':name', $name);
		$stmt->bindParam(':url', $url);
		$stmt->bindParam(':host', $host);
		$stmt->bindParam(':created_at', $time);
		$stmt->bindParam(':updated_at', $time);
	}

	$num=$pdo->exec($sql);//返回受影响的记录条数,num为int类型
	$insertid=$pdo->lastInsertId();//返回新增的主键ID
	if($num>0){
		echo '成功的添加了'.$num.'条记录,新增的主键ID是: '.$insertid;
	}else{
		echo '添加失败';
	}
}catch(PDOExcetption $e){
    die('操作失败'.$e->getMessage());


// $link = mysqli_connect(
// 	'localhost',
// 	'crawler',
// 	'root',
// 	'crawler'
// ) or die('数据库链接失败！');

$config = [
	'base_uri' => 'http://www.fang.com/',
	'timeout' => 4.0
];



$crawler = new CategoryAskCrawler($config);
$stmt = $crawler->getAllCategoryLinks();


dd($m);