<?php

header("Content-type: text/html; charset=utf-8");

require 'vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
// use Htf\Crawler\Libraries\BaseCrawler;

/**
 * 获取房天下资讯类的栏目分页列表URL
 * 该类只是测试爬取数据，方法命名、代码组织不够完善，后期优化
 */
class FangCrawler
{	
	public $client;
	public $URLArr = []; // 文章列表的链接
	public $articleURLArr = []; // 文章的链接
	public $i = 0; // 计数用，用于调试

	function __construct(Array $config)
	{
		$this->client = new Client($config);
	}

	// 获取对应URL的html
	public function getHTML($url)
	{
		$request = new Request('GET', $url);
		$response = $this->client->send($request);

		// 解决乱码问题
		$type = $response->getHeader('content-type'); 
		$parsed = Psr7\parse_header($type); 
		$original_body = (string)$response->getBody(); 

		// if (empty($parsed[0]['charset']) )
		$utf8_body = mb_convert_encoding($original_body, 'gbk', $parsed[0]['charset'] ?: 'gbk');
		// $utf8_body = mb_convert_encoding($original_body, 'UTF-8', $parsed[0]['charset'] ?: 'UTF-8');

		return $utf8_body;
		return (string)$response->getBody();
	}

	// 获取好房资讯列表的分页信息
	public function getFirstPageList($html)
	{
		$crawler = new Crawler($html);
		return $crawler->filter('body .infoBox-list ul li a')->each(function (Crawler $node, $i) {
			$url = $node->attr('href');
			$text = $node->text();
			return [trim($text) => trim($url)];
		});
	}

	// 过滤掉无关的url
	public function filter($pageList)
	{
		$pagesArr = [];

		foreach ($pageList as $key => $urlArr) {
			$urlArr2 = array_flip($urlArr);
			$url = array_shift($urlArr);
			$k = array_shift($urlArr2);
			if (is_numeric($k)) {
				$pagesArr[] = $url;
			}
		}

		return $pagesArr;
	}

	// 获取分类页的数组
	public function getPageList($html)
	{
		$pagesArr = $this->getFirstPageList($html);
		$pagesArr = $this->filter($pagesArr);
		$endPageURL = end($pagesArr);
		$this->getAllPages($pagesArr, $endPageURL);

		return $this->URLArr;
	}

	// 根据传入的链接获取所有的链接
	public function getAllPages($URLArr, $endPageURL)
	{
		$html = $this->getHTML($endPageURL);
		$pagesArrOri = $this->getFirstPageList($html);
		$pagesArr = $this->filter($pagesArrOri);
		$lastPageUrl = end($pagesArr);

		if (empty($lastPageUrl)) {
			$this->URLArr = $URLArr;
			return;
		}

		if ($this->i == 100) {
			$this->URLArr = $URLArr;
			return;
		}

		foreach ($pagesArr as $url) {
			if (!in_array($url, $URLArr)) {
				$URLArr[] = $url;
				$this->i +=1;
			}
		}

		$endPageURL = end($URLArr);
		self::getAllPages($URLArr, $endPageURL);
	}

	// 获取指定栏目列表中文章的a链接
	public function getArticleLinkFromList($html)
	{
		$crawler = new Crawler($html);
		return $crawler->filter('body .infoBox-list h3 a')->each(function (Crawler $node, $i) {
			return $node->attr('href');
			$url = $node->attr('href');
			$text = $node->text();
			return [trim($text) => trim($url)];
		});
	}

	// 根据文章列表的分页链接获取所有的文章链接
	public function getAllArticleURL($categoryListLink, $fangCrawler)
	{
		foreach ($categoryListLink as $categoryLink) {
			$html = $fangCrawler->getHTML($categoryLink);
			$articleLinkArr = $this->getArticleLinkFromList($html);

			foreach ($articleLinkArr as $link) {
				if (!in_array($link, $this->articleURLArr)) {
					$this->articleLinkArr[] = $link;
				}
			}			
		}

		return $this->articleLinkArr;
	}

	// 根据单个文章链接获取内容
	public function getArticleInfoByLink($link, $fangCrawler)
	{
		$html = $fangCrawler->getHTML($link);
		$crawler = new Crawler($html);
		$info['name'] = $crawler->filter('h1')->text();
		// $info['date'] = $crawler->filter('.assis-title')->text();

		return $info;
	}

	// 获取所有的文章信息
	public function getAllArticleInfo($articleLinkArr, $fangCrawler)
	{
		$infoArr = [];

		foreach ($articleLinkArr as $link) {
			$info = $this->getArticleInfoByLink($link, $fangCrawler);

			if (in_array($info, $infoArr)) {
				$infoArr[] = $info;
			}
		}

		return $infoArr;
	}
}

$config = [
	'base_uri' => 'http://www.fang.com/',
	'timeout' => 4.0
];

#############################
// $url = 'http://cd.news.fang.com/2018-09-14/29602695.htm';
// $fangCrawler = new FangCrawler($config);
// $html = $fangCrawler->getHTML($url);

// dd($html);
#############################


// 获取所有文章列表的链接
$url = 'http://cd.news.fang.com/gdxw.html';
$fangCrawler = new FangCrawler($config);
$html = $fangCrawler->getHTML($url);
$allPages = $fangCrawler->getPageList($html);

// 获取所有文章的链接
$allArticlePages = $fangCrawler->getAllArticleURL($allPages, $fangCrawler);

// 获取所有的文章信息
$fangCrawler->getArticleInfoByLink('http://cd.news.fang.com/2018-09-10/29558207.htm', $fangCrawler);
// $articleInfoArr = $fangCrawler->getAllArticleInfo($allArticlePages, $fangCrawler);

// dd($articleInfoArr);

// dd($info);
$articleLinkArr = array_slice($allArticlePages, 1, 5);

$info = [];

foreach ($articleLinkArr as $link) {
	$info[] = $fangCrawler->getArticleInfoByLink($link, $fangCrawler);
}

var_dump($articleLinkArr);
dd($info);

dd($articleLinkArr);

dd($allArticlePages);

dd($allPages);