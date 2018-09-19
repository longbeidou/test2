<?php

namespace Hft\Crawler\Libraries\www_fang_com;

use Hft\Crawler\Libraries\BaseCrawler;

/**
* 针对房天下的爬虫
* www.fang.com
*/
class FangCrawler extends BaseCrawler
{
	const HTTP_DOMAIN = 'http://www.fang.com';

	public function __construct(array $config)
	{
		parent::__construct($config);
	}

	// 将获取到的结果中的url加入域名
	public function addHttpDomainToResult($result, $tag = '')
	{
		$resultNew = [];

		foreach ($result['url'] as $key => $url) {
			$text = $result['text'][$key];
			$urlNew = self::HTTP_DOMAIN.$tag.$url;
			$resultNew['url'][] = $urlNew;
			$resultNew['text'][] = $text;
			$resultNew['both'][] = [$text => $urlNew];
		}

		return $resultNew;
	}
}