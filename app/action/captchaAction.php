<?php
/*
*	@captchaAction.php
*	Copyright (c)2013 Mallmold Ecommerce(HK) Limited. 
*	http://www.mallmold.com/
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*	
*	If you want to get an unlimited version of the program or want to obtain
*	additional services, please send an email to <service@mallmold.com>.
*/

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