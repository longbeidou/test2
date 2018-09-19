<?php

require './vendor/autoload.php';

use Hft\Crawler\Libraries\www_fang_com\CategoryAskCrawler;
use Hft\Crawler\Libraries\BaseCrawler;

$config = [
	'base_uri' => 'http://www.fang.com/',
	'timeout' => 4.0
];

$crawler = new CategoryAskCrawler($config);
// $html = $crawler->getAllCategoryLinks();
// $re = $crawler->getSolvedQuestionLinksByURL('http://www.fang.com/ask/class3/bj_30_yjj_1/');
// $m = $crawler->getAllInfoFromCategory();
$url = 'http://www.fang.com/ask/class3/default_30_yjj_1/';



$m = $crawler->getAllPaginationLink($url);

dd($m);