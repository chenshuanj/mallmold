<?php
/*
*	@paypal_express.php
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

require_once(APP_PATH .'model/paypal_pro.php');

class paypal_express extends paypal_pro
{
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_paypal_express')->get();
		$this->test_mode = $setting['test_mode'];
		$this->api_username = $setting['user'];
		$this->api_password = $setting['password'];
		$this->api_signature = $setting['signature'];
		
		if($this->test_mode == 1){
			$this->env_url = $this->env_url_test;
		}
		
		$this->model = 'paypal_express';
	}
}
?>