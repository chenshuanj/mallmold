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
	private $data = array();
	private $localpay = false;
	private $repay = true;
	
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
	
	public function can_localpay()
	{
		return $this->localpay;
	}
	
	public function can_repay()
	{
		return $this->repay;
	}
	
	public function validate()
	{
		return true;
	}
	
	public function set_pay_params($order)
	{
		$pay_params = array(
			'service'				=> 'create_direct_pay_by_user',
			'partner'				=> trim($this->partner),
			'payment_type'			=> 1,
			'notify_url'			=> url('notify/notify_alipay'),
			'return_url'			=> url('order/success?order_id='.$order['order_id']),
			'seller_email'			=> trim($this->seller_email),
			'out_trade_no'			=> $order["order_id"],
			'subject'				=> '订单：'.$order["order_id"],
			'total_fee'				=> $order["total_amount"],
			'body'					=> '订单：'.$order["order_id"],
			'show_url'				=> url('account/orderview?order_id='.$order['order_id']),
			'exter_invoke_ip'		=> $this->load('lib/visitor')->get_ip(),
			'_input_charset'		=> 'utf-8',
		);
		
		//sign
		$pay_params['sign_type'] = 'MD5';
		$pay_params['sign'] = $this->createsign($pay_params);
		
		foreach($pay_params as $k=>$v){
			$pay_params[$k] = urlencode($v);
		}
		
		$this->data = $pay_params;
		return true;
	}
	
	public function get_form()
	{
		return array(
			'action' => $this->gateway.'?_input_charset=utf-8',
			'data' => $this->data,
		);
	}
	
	public function verify()
	{
		$pay_params = array();
		foreach($_POST as $key => $value){
			if($key == "sign" || $key == "sign_type" || $val == ""){
				continue;
			}
			$pay_params[$key] = $value;
		}
		$sign = $this->createsign($pay_params);
		$status = false;
		if($sign == $_POST['sign']){
			if($_POST['notify_id']){
				$url = $this->verify_url.'?partner='.trim($this->partner).'&notify_id='.$_POST['notify_id'];
				$verify = $this->load('lib/http')->get($url);
				if(strtolower($verify) != 'true'){
					$this->model('payment')->error_msg = 'Verification return false: '.$verify;
				}else{
					$status = true;
				}
			}else{
				$status = true;
			}
		}else{
			$this->model('payment')->error_msg = 'Verification failed: '.var_export($_POST, true);
		}
		
		if($status == false){
			$this->model('payment')->error_log($_POST['out_trade_no'], 'alipay');
		}
		
		return $status;
	}
	
	private function createsign($pay_params)
	{
		ksort($pay_params);
		$para = '';
		foreach($pay_params as $k=>$v){
			$para .= ($para ? '&' : '').$k.'='.$v;
		}
		if(get_magic_quotes_gpc()){
			$para = stripslashes($para);
		}
		return md5($para.$this->key);
	}
}
?>