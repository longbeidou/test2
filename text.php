<?php

/**
* 
*/
class Welcome extends Thread
{
	public $para;
	
	public function __construct($para)
	{
		$this->para = $para;
	}

	public function run() {
		print_r(sprintf("The parameter is: %s\n", $this->para));
	}
}

$welcome = new Welcome('hello word1');
$welcome = new Welcome('hello word2');

if ($welcome->start()) {
	printf("thread #%s say: %s\n", $welcome->getThreadId(), $welcome->join());
}