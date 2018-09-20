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
	die("mysql connect failed!更多信息如下：\n ".$e->getMessage());	
}

// 获取所有分类信息
try{
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "SELECT url, id FROM ask_cate";
	$listInfo = $pdo->query($sql);
	$cateInfo = $listInfo->fetchAll(PDO::FETCH_ASSOC);
	
	if (count($cateInfo) == 0) {
		$pdo = null;
		die('上级目录没有获取到URL');
	}
}catch(PDOExcetption $e){
	$pdo = null;
    die('操作失败'.$e->getMessage());
}

// 爬虫的状态
$crawlerStatus = true; 

foreach ($cateInfo as $key => $cate) {
	// 抓取分页信息
	try {
		$crawler = new CategoryAskCrawler($config);		
		$pageInfo = $crawler->getAllPaginationInfo($cate['url']);
	} catch (Exception $e) {
		var_dump('爬取信息失败！');
		$crawlerStatus = false;
	}

	if ($crawlerStatus) { // 正确爬到数据
		// 将分页信息写入数据库
		try{
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sql = "INSERT INTO ask_cate_list VALUE (null, :name, {$cate['id']}, :url, :host, 3, :created_at, :updated_at)";
			$list = $pdo->prepare($sql);
			$list->bindParam(':name', $name);
			$list->bindParam(':url', $url);
			$list->bindParam(':host', $host);
			$list->bindParam(':created_at', $created_at);
			$list->bindParam(':updated_at', $updated_at);

			foreach ($pageInfo['both'] as $i => $infoBoth) {
				$time = Carbon::now();
				$name = $pageInfo['text'][$i];
				$url = $pageInfo['url'][$i];
				$host = HOST_NAME;
				$status = 3;
				$created_at = $time;
				$updated_at = $time;
				$list->execute();
				var_dump('成功插入id为'.$pdo->lastInsertId().'的数据');
			}
		}catch(PDOExcetption $e){
			$pdo = null;
		    var_dump('插入分页信息失败'.$e->getMessage());
		}
	} else {
		$crawlerStatus = true; 
	}
}

$pdo = null;
echo "爬取信息完毕\n";