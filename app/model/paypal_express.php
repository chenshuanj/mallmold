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

class paypal_express extends model
{
	private $test_mode = 0;
	private $env_url = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
	private $env_url_test = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
	private $nvp_url = 'https://api-3t.paypal.com/nvp';
	private $nvp_url_test = 'https://api-3t.sandbox.paypal.com/nvp';
	private $token;
	private $data = array();
	private $localpay = false;
	private $repay = true;
	
	private $api_username;
	private $api_password;
	private $api_signature;
	
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
			$this->nvp_url = $this->nvp_url_test;
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
		$country_code = $this->db->table('country')->where("id=".$order["shipping_address"]['country_id'])->getval('code');
		$state_code = $this->db->table('region')->where("region_id=".$order["shipping_address"]['region_id'])->getval('code');
		$discount_amount = $this->model('coupon')->get_money($order["coupon_id"]);
		
		$pay_params = array(
			'METHOD'				=> 'SetExpressCheckout',
			'PAYMENTACTION'			=> 'Sale',
			'SHIPTONAME'			=> $order["shipping_address"]['firstname'].' '.$order["shipping_address"]['lastname'],
			'SHIPTOSTREET'			=> $order["shipping_address"]['address'],
			'SHIPTOCITY'			=> $order["shipping_address"]['city'],
			'SHIPTOSTATE'			=> $state_code,
			'SHIPTOCOUNTRYCODE'		=> $country_code,
			'SHIPTOZIP'				=> $order["shipping_address"]['postcode'],
			'CURRENCYCODE'			=> $order['currency'],
			'ITEMAMT'				=> $order["goods_amount"],
			'AMT'					=> $order["total_amount"],
			'MAXAMT'				=> $order["total_amount"],
			'TAXAMT'				=> $order["tax_fee"],
			'SHIPPINGAMT'			=> $order["shipping_fee"],
			'SHIPDISCAMT'			=> 0-$discount_amount,
			'ReturnUrl'				=> url('order/paypal_express_review?order_id='.$order['order_id']),
			'CANCELURL'				=> url('account/orderview?order_id='.$order['order_id']),
		);
		
		$n = 0;
		foreach($order['goods'] as $goods){
			//L_NUMBER0 sku
			$name_key = 'L_NAME'.$n;
			$quantity_key = 'L_QTY'.$n;
			$price_key = 'L_AMT'.$n;
			
			$pay_params[$name_key] = $goods['goods_name'];
			$pay_params[$price_key] = $goods['price'];
			$pay_params[$quantity_key] = $goods['quantity'];
			
			$n++;
		}
		
		$res = $this->send_request($pay_params);
		if($res){
			$this->token = $res['TOKEN'];
			return true;
		}else{
			return false;
		}
	}
	
	public function get_form()
	{
		return array(
			'action' => $this->env_url.$this->token,
		);
	}
	
	public function verify()
	{
		$status = false;
		$token = $_REQUEST['token'];
		if(!$token){
			$this->model('payment')->error_msg = 'Parameter error: Missing token';
		}else{
			$pay_params = array(
				'METHOD'		=> 'GetExpressCheckoutDetails',
				'TOKEN'			=> $token,
			);
			$res = $this->send_request($pay_params);
			if($res){
				$this->data = $res;
				$status = true;
			}
		}
		return $status;
	}
	
	public function pay()
	{
		$pay_params = array(
			'METHOD'		=> 'DoExpressCheckoutPayment',
			'PAYMENTACTION'	=> 'Sale',
			'TOKEN'			=> $_POST['token'],
			'PAYERID'		=> $_POST['payer_id'],
			'AMT'			=> $_POST['TotalAmount'],
			'CURRENCYCODE'	=> $_POST['currCodeType'],
			'IPADDRESS'		=> $this->model('visitor')->get_ip(),
		);
		
		$res = $this->send_request($pay_params);
		if($res){
			$this->data = $res;
			return true;
		}else{
			return false;
		}
	}
	
	public function getdata()
	{
		return $this->data;
	}
	
	private function send_request($pay_params)
	{
		$pay_params['VERSION'] = '65.1';
		$pay_params['USER'] = $this->api_username;
		$pay_params['PWD'] = $this->api_password;
		$pay_params['SIGNATURE'] = $this->api_signature;
		
		$content = '';
		foreach($pay_params as $k=>$v){
			$content .= ($content ? '&' : '').$k.'='.urlencode($v);
		}
		
		$response = $this->load('lib/http')->post($this->nvp_url, $content);
		parse_str($response, $res);
		$ack = strtoupper($res["ACK"]);
		if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING'){
			return $res;
		}else{
			$this->model('payment')->error_msg = $res['L_SHORTMESSAGE0'].'<br/>'.$res['L_LONGMESSAGE0'];
			return false;
		}
	}
}
?>