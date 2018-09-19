<?php

namespace Hft\Crawler\Libraries;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Psr7\Request;

/**
* 爬虫模板
*/
class BaseCrawler
{
	public $client;

	public function __construct(Array $config)
	{
		$this->client = new Client($config);
	}

	// 查询和操作HTML以及XML文档的类
	public function crawler($html)
	{
		return new Crawler($html);
	}

	// 根据URL获取html内容
	public function getHTML($url)
	{
		$request = new Request('GET', $url);
		$response = $this->client->send($request);

		return (string)$response->getBody();
	}

	// 从html代码中获取a链接以及a链接对应的文本
	public function getLinkInfoFromHTML(String $html, String $tag, $method = 'filter')
	{
		if ($method === 'filter') { // 根据css的规则获取信息
			$linkInfoArr = $this->filter($html, $tag);
		} else {
			$linkInfoArr = $this->filterXPath($html, $tag);
		}

		return $linkInfoArr;
	}

	// 根据css的规则获取链接
	public function filter ($html, $tag)
	{
		$crawler = $this->crawler($html);
		$linkInfoArr = ['url' => [], 'text' => [], 'both' => []];
		$crawler->filter($tag)->each(function (Crawler $node, $i) use(&$linkInfoArr) {
			$url = $node->attr('href');
			$url = trim($url);
			$text = $node->text();
			$text = trim($text);

			if ($linkInfoArr === [] && !empty($url)) {
				$linkInfoArr['url'][] = $url;
				$linkInfoArr['text'][] = $text;
				$linkInfoArr['both'][] = [$text => $url];
			} elseif (!in_array($url, $linkInfoArr['url']) && !empty($url)) {
				$linkInfoArr['url'][] = $url;
				$linkInfoArr['text'][] = $text;
				$linkInfoArr['both'][] = [$text => $url];
			}
		});

		return $linkInfoArr;
	}

	// 根据XPath的规则获取链接
	public function filterXPath ($html, $tag)
	{
		$crawler = $this->crawler($html);
		$linkInfoArr = [];
		$crawler->filterXPath($tag)->each(function (Crawler $node, $i) use(&$linkInfoArr) {
			$url = $node->attr('href');
			$url = trim($url);
			$text = $node->text();
			$text = trim($text);

			if ($linkInfoArr === [] && !empty($url)) {
				$linkInfoArr['url'][] = $url;
				$linkInfoArr['text'][] = $text;
				$linkInfoArr['both'][] = [$text => $url];
			} elseif (!in_array($url, $linkInfoArr['url']) && !empty($url)) {
				$linkInfoArr['url'][] = $url;
				$linkInfoArr['text'][] = $text;
				$linkInfoArr['both'][] = [$text => $url];
			}
		});

		return $linkInfoArr;
	}

	// 获取数组第一元素的值
	public function array_first_value ($array)
	{
		return array_shift($array);
	}

	// 获取数组元素的最后一个元素的值
	public function array_last_value ($array)
	{
		$result = array_slice($array, -1, 1);
		
		return array_shift($result);
	}
}