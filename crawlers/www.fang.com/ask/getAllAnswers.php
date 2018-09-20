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
	// 从数据库获取信息
	try{
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql = "SELECT url, id FROM ask_cate_que LIMIT {$num},100";
		$num += 100;
		$listInfo = $pdo->query($sql);
		$quesLinkInfo = $listInfo->fetchAll(PDO::FETCH_ASSOC);

		if (count($quesLinkInfo) == 0) {
			$on =  false;
			$pdo = null;
			die('上级目录没有获取到URL');
		}
	}catch(PDOExcetption $e){
		$pdo = null;
	    die('操作失败'.$e->getMessage());
	}

	// 爬虫的状态
	$crawlerStatus = true; 

	// 根据数据库的信息抓信息
	foreach ($quesLinkInfo as $key => $link) {
		// 爬取文章数据
		try {
			$crawler = new CategoryAskCrawler($config);
			$ansInfo = $crawler->getAnswerDetails($link['url']);
		} catch (Exception $e) {
			var_dump("爬取问答信息失败!");
			$crawlerStatus = false; 
		}

		if ($crawlerStatus) { // 爬虫能够顺利爬行
			$insertSQLStatus = true; // 插入数据库的状态

			// 将爬取的数据写入数据库
			try{
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$sql = "INSERT INTO ask_cate_ans VALUE (null, {$link['id']}, :question, :host, 3, :created_at, :updated_at)";
				$list = $pdo->prepare($sql);
				$list->bindParam(':question', $ansInfo['question']);
				$list->bindParam(':host', $host);
				$list->bindParam(':created_at', $created_at);
				$list->bindParam(':updated_at', $updated_at);

				// 插入问题
				$time = Carbon::now();
				$host = HOST_NAME;
				$created_at = $time;
				$updated_at = $time;
				$list->execute();
				$topInsertId = $pdo->lastInsertId();
				var_dump('成功插入id为'.$insertId.'的数据');
			}catch(PDOExcetption $e){
			    var_dump('插入问题失败'.$e->getMessage());
			    $insertSQLStatus = false; 
			}

			if ($insertSQLStatus) { // 上面的数据库能够正常的插入
				// 插入问答的详情
				try {
					foreach ($ansInfo['nick'] as $key => $nick) {
						if (substr_count($nick, '房天下') == 0) {
							$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							$sql = "INSERT INTO ask_cate_ans_data VALUE (null, {$topInsertId}, :nick, :content, :trueTime, :host, 3, :created_at, :updated_at)";
							$list = $pdo->prepare($sql);
							// $list->bindParam(':askCateAnsId', $insertId);
							$list->bindParam(':nick', $nick);
							$list->bindParam(':content', $ansInfo['content'][$key]);
							$list->bindParam(':trueTime', $ansInfo['trueTime'][$key]);
							$list->bindParam(':host', $host);
							$list->bindParam(':created_at', $created_at);
							$list->bindParam(':updated_at', $updated_at);

							// 插入问题
							$time = Carbon::now();
							$host = HOST_NAME;
							$created_at = $time;
							$updated_at = $time;
							$list->execute();
							$insertId = $pdo->lastInsertId();
							var_dump('成功插入id为'.$insertId.'的评论详情数据');
						}
					}
				} catch (Exception $e) {
					var_dump('插入回答详情失败'.$e->getMessage());
				}
			} else {
				$insertSQLStatus = true;
			}
		} else {
			$crawlerStatus = true; 
		}
	}
}