<?php
/*
*	@alipay.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

class alipay extends model
{
	private $test_mode = 0;
	private $gateway = 'https://mapi.alipay.com/gateway.do';
	private $gateway_test = 'http://openapi.alipaydev.com/gateway.do';
	private $verify_url = 'http://notify.alipay.com/trade/notify_query.do';
	private $refund = false;
	
	public $seller_email;
	public $partner;
	public $key;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_alipay')->get();
		$this->test_mode = $setting['test_mode'];
		$this->seller_email = $setting['seller_email'];
		$this->partner = $setting['partner'];
		$this->key = $setting['key'];
		
		if($this->test_mode == 1){
			$this->gateway = $this->gateway_test;
		}
	}
	
	public function can_refund()
	{
		return $this->refund;
	}
}
?>