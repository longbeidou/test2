<?php

namespace Hft\Crawler\Libraries\www_fang_com;

use Hft\Crawler\Libraries\www_fang_com\FangCrawler;

/**
* 针对房天下问答栏目的爬虫
*/
class CategoryAskCrawler extends FangCrawler
{	
	const URL = 'http://www.fang.com/ask/';

	public $questionSolvedLinks = []; // 已解决问题的问题列表
	public $paginationSolvedLinks = []; // 已解决问题的分页链接
	public $tmp; // 方法临时使用的临时变量，不要删除

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
sleep(2);
		$numTwo = count($this->paginationSolvedLinks);
$this->tmp += 1;
if ($this->tmp === 30) {
	dd($this->paginationSolvedLinks);
}
		if ($numTwo !== $numOne) {
			// 获取最后个第一个链接
			$lastLink = $this->array_last_value($linkNew);
			self::getAllPaginationLink($lastLink);
		}

		return $this->paginationSolvedLinks;
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
}