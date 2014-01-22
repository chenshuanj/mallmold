<?php


class captchaAction extends action
{
	public function __construct()
    {
		parent::__construct();
	}
	
	public function index()
	{
		$code = $this->load('lib/captcha')->getcode();
		if(!$code || $_GET['update']){
			$this->load('lib/captcha')->set_captcha();
			$code = $this->load('lib/captcha')->getcode();
		}
		
		$this->load('lib/captcha')->putimg($code);
		return;
	}
}

?>