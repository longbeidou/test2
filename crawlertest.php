<?php

/**
* 
*/
class Request extends \Thread
{
	public $url;
	public $response;

	public function __construct($url)
	{
		$this->url = $url;
	}

	public function run()
	{
		echo $this->url.microtime()."\t\n";
		$this->response = file_get_contents($this->url);
		echo $this->url.microtime()."\t\n";
	}
}

$chG = new Request("https://www.52010000.cn");
$chB = new Request("https://www.fang.com");
$chG ->start();
$chB ->start();
$chG->join();
$chB->join();

$gl = $chG->response;
$bd = $chB->response;
echo strlen($gl)."\t\n";
echo strlen($bd)."\t\n";
sleep(2);
var_dump(datetime());