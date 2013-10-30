<?php
/*
*	@paypal_pro.php
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

class paypal_pro extends model
{
	private $test_mode = 0;
	private $nvp_url = 'https://api-3t.paypal.com/nvp';
	private $nvp_url_test = 'https://api-3t.sandbox.paypal.com/nvp';
	private $data = array();
	private $localpay = true;
	private $repay = false;
	
	private $api_username;
	private $api_password;
	private $api_signature;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_paypal_pro')->get();
		$this->test_mode = $setting['test_mode'];
		$this->api_username = $setting['user'];
		$this->api_password = $setting['password'];
		$this->api_signature = $setting['signature'];
		
		if($this->test_mode == 1){
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
		//check credit card
		$card_type = trim($_POST['card_type']);
		$card_number = trim($_POST['card_number']);
		$card_exp_month = trim($_POST['card_exp_month']);
		$card_exp_year = trim($_POST['card_exp_year']);
		$card_code = trim($_POST['card_code']);
		
		if(!$card_type){
			$this->model('payment')->error_msg = 'Credit Card type is required';
			return false;
		}
		if(!$card_number){
			$this->model('payment')->error_msg = 'Credit Card Number is required';
			return false;
		}
		if(!$this->load('lib/filter')->is_number($card_number)){
			$this->model('payment')->error_msg = 'Wrong Credit Card Number';
			return false;
		}
		if(!$card_exp_month || !$card_exp_year){
			$this->model('payment')->error_msg = 'Credit Card Expiration Date is required';
			return false;
		}
		if(!$card_code){
			$this->model('payment')->error_msg = 'Credit Card Verification Code is required';
			return false;
		}
		if(!$this->load('lib/filter')->is_number($card_code)){
			$this->model('payment')->error_msg = 'Wrong Credit Card Verification Code';
			return false;
		}
		
		$this->data['card_type'] = $card_type;
		$this->data['card_num'] = $card_number;
		$this->data['exp_date'] = $card_exp_month.$card_exp_year;
		$this->data['card_code'] = $card_code;
		
		return true;
	}
	
	public function localpay($order_data)
	{
		$country_code = $this->db->table('country')->where("id=".$order_data['address']['bill_country_id'])->getval('code');
		$state_code = $this->db->table('region')->where("region_id=".$order_data['address']['bill_region_id'])->getval('code');
		
		$pay_params = array(
			'METHOD'			=> 'doDirectPayment',
			'PAYMENTACTION'		=> 'Sale',
			'AMT'				=> $order_data['checkout']['total'],
			'CREDITCARDTYPE'	=> $this->data['card_type'],
			'ACCT'				=> $this->data['card_num'],
			'EXPDATE'			=> $this->data['exp_date'],
			'CVV2'				=> $this->data['card_code'],
			'FIRSTNAME'			=> $order_data['address']['bill_firstname'],
			'LASTNAME'			=> $order_data['address']['bill_lastname'],
			'STREET'			=> $order_data['address']['bill_address'].$order_data['address']['bill_address2'],
			'CITY'				=> $order_data['address']['city'],
			'STATE'				=> $state_code,
			'ZIP'				=> $order_data['address']['bill_postcode'],
			'COUNTRYCODE'		=> $country_code,
			'CURRENCYCODE'		=> $order_data['currency'],
		);
		
		$res = $this->send_request($pay_params);
		if($res){
			$status = $this->model('payment')->pay_status('processing');
			$this->model('payment')->status = $status;
			
			//pay log
			$remark = 'ACK:'.$res['ACK'].'; CORRELATIONID:'.$res['CORRELATIONID'].'; BUILD:'.$res['BUILD'];
			$data = array(
				'type' => 1,
				'order_sn' => $order_data['order_sn'],
				'model' => 'paypal_pro',
				'track_id' => $res['TRANSACTIONID'],
				'currency' => $res['CURRENCYCODE'],
				'money' => $res['AMT'],
				'remark' => $remark,
				'time' => time()
			);
			$this->db->table('payment_log')->insert($data);
			$this->model('payment')->message = 'pay success';
			return true;
		}else{
			$this->model('payment')->error_log(0, 'paypal_pro');
			return false;
		}
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
		if($ack == 'SUCCESS'){
			return $res;
		}else{
			$this->model('payment')->error_msg = $res['L_SHORTMESSAGE0'].'<br/>'.$res['L_LONGMESSAGE0'];
			return false;
		}
	}
}
?>