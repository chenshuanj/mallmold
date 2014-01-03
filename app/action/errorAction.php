<?php


require Action('common');

class errorAction extends commonAction
{
	public function __404()
	{
		header("HTTP/1.1 404 Not Found");
		$this->view('error/404.html');
	}
}

?>