<?php

namespace Hft\Crawler\Libraries\www_fang_com;

use Hft\Crawler\Libraries\www_fang_com\FangCrawler;
use Symfony\Component\DomCrawler\Crawler;

/**
* 针对房天下问答栏目的爬虫
*/
class CategoryAskCrawler extends FangCrawler
{	
	const URL = 'http://www.fang.com/ask/';

	public $questionSolvedLinks = []; // 已解决问题的问题列表
	public $paginationSolvedLinks = []; // 已解决问题的分页链接
	public $paginationSolvedInfo = ['text' => [], 'url' => [], 'both' => []]; // 已解决问题的分页链接+内容
	public $tmp; // 方法临时使用的临时变量，不要删除
	public $no = 0; // 临死使用的变量，不要删除

	// 获取所有问答栏目分类的信息
	public function getAllCategoryLinks ()
	{
		$html = $this->getHTML(self::URL);
		$categoryLinks = $this->getLinkInfoFromHTML($html, '.sec-menu a');

		return $this->filterInvalidInfoFromCategoryInfo($categoryLinks);
	}

	// 根据问题列表链接获取已解决问题的列表信息（问题详情页链接+pagination链接信息）
	public function getSolvedQuestionLinksByURL ($url)
	{
		$html = $this->getHTML($url);
		$questionLinks = $this->getLinkInfoFromHTML($html, '.question-list a');
		$paginationLinks = $this->getLinkInfoFromHTML($html, '.pagebar-right a');
		$questionLinks = $this->addHttpDomainToResult($questionLinks);
		$paginationLinks = $this->addHttpDomainToResult($paginationLinks);

		return [
			'quesLink' => $questionLinks,
			'pagiLink' => $paginationLinks
		];
	}

	// 获取所有栏目分类对应的问题链接以及分页链接
	public function getAllInfoFromCategory()
	{
		$cateLink = $this->getAllCategoryLinks();

		foreach ($cateLink['url'] as $key => $url) {
			$result = $this->getSolvedQuestionLinksByURL($url);

			if (!empty($result)) {
				// 获取问题的链接
				foreach ($result['quesLink']['url'] as $link) {
					if (!in_array($link, $this->questionSolvedLinks)) {
						$this->questionSolvedLinks[] = $link;
					}					
				}
			}

			// $this->paginationSolvedLinks[] = $url;
			$allPaginationLinks = $this->getAllPaginationLink($url);
 		}
	}

	// 根据问题列表链接获取所有的分页的链接
	public function getAllPaginationLink ($url)
	{
		// 根据给定的url获取页面链接信息
		$result = $this->getSolvedQuestionLinksByURL($url);
		$pagiLink = $result['pagiLink'];
		// 获取有效的分页链接
		$linkNew = $this->getValideLinkFromPagiLink($pagiLink);
		$numOne = count($this->paginationSolvedLinks);

		// 将新数组写入变量
		foreach ($linkNew as $url) {
			if (!in_array($url, $this->paginationSolvedLinks)) {
				$this->paginationSolvedLinks[] = $url;
			}
		}

		$numTwo = count($this->paginationSolvedLinks);

		if ($numTwo !== $numOne) {
			// 获取最后个第一个链接
			$lastLink = $this->array_last_value($linkNew);
			self::getAllPaginationLink($lastLink);
		}

		return $this->paginationSolvedLinks;
	}

	// 根据问题列表链接获取所有的分页的链接+分页的文字
	public function getAllPaginationInfo ($url)
	{
		// 根据给定的url获取页面链接信息
		$result = $this->getSolvedQuestionLinksByURL($url);
		$pagiLink = $result['pagiLink'];
		// 获取有效的分页链接+信息
		$linkInfoNew = $this->getValideLinkFromPagiInfo($pagiLink);
		$numOne = count($this->paginationSolvedInfo['url']);

		// sleep(5);

		// 将新数组写入变量
		foreach ($linkInfoNew['url'] as $key => $url) {
			if (!in_array($url, $this->paginationSolvedInfo['url'])) {
				$this->paginationSolvedInfo['url'][] = $url;
				$this->paginationSolvedInfo['text'][] = $linkInfoNew['text'][$key];
				$this->paginationSolvedInfo['both'][] = [$linkInfoNew['text'][$key] => $url];
				var_dump($url);
				var_dump($linkInfoNew['text'][$key]);
			}
		}

		$numTwo = count($this->paginationSolvedInfo['url']);

		if ($numTwo !== $numOne) {
			// 获取最后个第一个链接
			$lastLink = $this->array_last_value($linkInfoNew['url']);
			self::getAllPaginationInfo($lastLink);
		}

		return $this->paginationSolvedInfo;
	}

	// 获取有效的分页链接+信息
	public function getValideLinkFromPagiInfo($pagiLink)
	{
		$info = [];

		foreach ($pagiLink['url'] as $key => $url) {
			if (
				$pagiLink['text'][$key] !== '首页'
			&&	$pagiLink['text'][$key] !== '上一页'
			&&	$pagiLink['text'][$key] !== '下一页'
			&&	$pagiLink['text'][$key] !== '尾页'
			) {
				$info['url'][] = $url;
				$info['text'][] = $pagiLink['text'][$key];
				$info['both'][] = $pagiLink['both'][$key];
			}
		}

		return $info;
	}

	// 获取有效的分页链接
	public function getValideLinkFromPagiLink($pagiLink)
	{
		$info = [];

		foreach ($pagiLink['url'] as $key => $url) {
			if (
				$pagiLink['text'][$key] !== '首页'
			&&	$pagiLink['text'][$key] !== '上一页'
			&&	$pagiLink['text'][$key] !== '下一页'
			&&	$pagiLink['text'][$key] !== '尾页'
			) {
				$info[] = $url;
			}
		}

		return $info;
	}

	// 过滤掉无用的a链接信息
	public function filterInvalidInfoFromCategoryInfo (Array $categoryInfo)
	{
		$validInfo = [];

		foreach ($categoryInfo['url'] as $url) {
			if ($url !== 'http://world.fang.com/') {
				$validInfo['url'][] = self::HTTP_DOMAIN.$url;
			}
		}

		foreach ($categoryInfo['text'] as $text) {
			if ($text !== '海外') {
				$validInfo['text'][] = $text;
			}
		}

		foreach ($categoryInfo['both'] as $both) {
			$bothOri = $both;
			$bothOriRez = array_flip($bothOri);
			$text = array_shift($bothOriRez);
			$url = array_shift($both);

			if ($url !== 'http://world.fang.com/') {
				$validInfo['both'][] = [
					$text => self::HTTP_DOMAIN.$url
				];
			}
		}

		return $validInfo;
	}

	// 根据问题列表的链接获取问题的内容以及对应的链接
	public function getQuestionListInfo($url)
	{
		$html = $this->getHTML($url);
		$questionLinks = $this->getLinkInfoFromHTML($html, '.question-list a');
		$questionLinks = $this->addHttpDomainToResult($questionLinks);
		sleep(5);

		return $questionLinks;
	}

	// 根据问题链接获取问题详情页信息
	public function getAnswerDetails($url)
	{
		$html = $this->getHTML($url);
		$htmlBak = $html;
		$question = $this->getAnsBaseInfo($html, 'h1');
		$nick = $this->getAnsData($htmlBak, '.ans-name');
		$content = $this->getAnsData($htmlBak, '.Ans-text-part');
		$trueTime = $this->getAnsData($htmlBak, '.ansTime');
		
		return [
			'question' => $question,
			'nick' => $nick,
			'content' => $content,
			'trueTime' => $trueTime,
		];
	}

	// 获取问题详情页基本信息
	public function getAnsBaseInfo($html, $tag)
	{
		$crawler = $this->crawler($html)->filter($tag);
		$text = $crawler->text();

		return trim($text);
	}

	// 获取问题详情页基本信息
	public function getAnsData($html, $tag)
	{
		$info = [];

		$this->crawler($html)->filter($tag)->each(function(Crawler $node, $i) use(&$info) {
			$str = $node->text();
			$str = trim($str);

			if ($str == '') {
				$str = '44';
			}

			$info[] = $str;
		});

		return $info;
	}
}