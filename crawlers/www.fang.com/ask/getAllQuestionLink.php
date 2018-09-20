<?php

require __DIR__.'/../../../vendor/autoload.php';

use Hft\Crawler\Libraries\www_fang_com\CategoryAskCrawler;
use Hft\Crawler\Libraries\BaseCrawler;
use Carbon\Carbon;

define('HOST_NAME', 'http://www.fang.com');

$config = [
	'base_uri' => 'http://www.fang.com/',
	'timeout' => 4.0
];

// try {
// 	$crawler = new CategoryAskCrawler($config);
// 	$info = $crawler->getAllCategoryLinks();
// } catch (Exception $e) {
// 	die("爬取信息失败!");
// }

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

// 是否中断抓取的开关
$on = true;
$num = 0;

// 从数据库获取数据然后抓数据
while ($on) {
	// 获取问题列表分页链接信息
	try{
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql = "SELECT url, id FROM ask_cate_list LIMIT {$num},500";
		$num += 100;
		$listInfo = $pdo->query($sql);
		$quesLinkInfo = $listInfo->fetchAll(PDO::FETCH_ASSOC);

		if (count($quesLinkInfo) == 0) {
			$pdo = null;
			die('上级目录没有获取到URL');
		}
	}catch(PDOExcetption $e){
		$pdo = null;
	    die('操作失败'.$e->getMessage());
	}

	// 爬虫的状态
	$crawlerStatus = true; 

	// 爬取数据并写入数据库
	foreach ($quesLinkInfo as $key => $link) {
		// 爬取文章数据
		try {
			$crawler = new CategoryAskCrawler($config);
			$linkInfo = $crawler->getQuestionListInfo($link['url']);
		} catch (Exception $e) {
			var_dump("爬取信息失败!");
			$crawlerStatus = false;
		}

		if ($crawlerStatus) { // 爬虫处于正常情况
			// 将爬取的数据写入数据库
			foreach ($linkInfo['url'] as $key => $url) {
				try{
					$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$sql = "INSERT INTO ask_cate_que VALUE (null, {$link['id']}, :url, :host, 3, :created_at, :updated_at)";
					$list = $pdo->prepare($sql);
					$list->bindParam(':url', $url);
					$list->bindParam(':host', $host);
					$list->bindParam(':created_at', $created_at);
					$list->bindParam(':updated_at', $updated_at);

					foreach ($linkInfo['url'] as $i => $qurl) {
						$time = Carbon::now();
						$url = $qurl;
						$host = HOST_NAME;
						$created_at = $time;
						$updated_at = $time;
						$list->execute();
						var_dump('成功插入id为'.$pdo->lastInsertId().'的数据');
					}
				}catch(PDOExcetption $e){
				    var_dump('操作失败'.$e->getMessage());
				}
			}
		} else {
			$crawlerStatus = true;
		}
	}
}

$pdo = null;
echo '爬虫工作完毕';