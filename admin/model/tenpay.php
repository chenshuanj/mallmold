<?php
/*
*	@tenpay.php
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

class tenpay extends model
{
	private $test_mode = 0;
	private $api_address = 'https://api.tenpay.com';
	private $api_address_test = 'https://sandbox.tenpay.com/api';
	private $refund = false;
	
	public $appid;
	public $key;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_tenpay')->get();
		$this->test_mode = $setting['test_mode'];
		$this->appid = $setting['appid'];
		$this->key = $setting['key'];
		
		if($this->test_mode == 1){
			$this->api_address = $this->api_address_test;
		}
	}
	
	public function can_refund()
	{
		return $this->refund;
	}
}
?>