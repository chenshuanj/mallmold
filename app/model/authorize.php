<?php
/*
*	@authorize.php (AIM)
*	Copyright (c)2013-2014 Mallmold Ecommerce(HK) Limited. 
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

class authorize extends model
{
	private $test_mode = 0;
	private $api_url = 'https://secure.authorize.net/gateway/transact.dll';
	private $api_url_test = 'https://test.authorize.net/gateway/transact.dll';
	private $api_id;
	private $api_key;
	private $localpay = true;
	private $repay = false;
	
	private $card_num;
	private $exp_date;
	private $card_code;
	
	private $x_delim_char = ',';
	private $x_encap_char = '|';
	
	const APPROVED = 1;
    const DECLINED = 2;
    const ERROR = 3;
    const HELD = 4;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_authorize')->get();
		$this->test_mode = $setting['test_mode'];
		$this->api_id = $setting['api_id'];
		$this->api_key = $setting['api_key'];
		
		if($this->test_mode == 1){
			$this->api_url = $this->api_url_test;
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
		$card_number = trim($_POST['x_card_number']);
		$card_exp_month = trim($_POST['x_card_exp_month']);
		$card_exp_year = trim($_POST['x_card_exp_year']);
		$card_code = trim($_POST['x_card_code']);
		
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
		
		$this->card_num = $card_number;
		$this->exp_date = $card_exp_month.'/'.$card_exp_year;
		$this->card_code = $card_code;
		
		return true;
	}
	
	public function localpay($order_data)
	{
		$country_code = $this->db->table('country')->where("id=".$order_data['address']['bill_country_id'])->getval('code');
		$state_code = $this->db->table('region')->where("region_id=".$order_data['address']['bill_region_id'])->getval('code');
		
		$data = array(
			'x_version'			=> '3.1',
			'x_delim_data'		=> 'TRUE',
			'x_delim_char'		=> $this->x_delim_char,
			'x_encap_char'		=> $this->x_encap_char,
			'x_relay_response'	=> 'FALSE',
			
			'x_login'			=> $this->api_id,
			'x_tran_key'		=> $this->api_key,
			
			'x_type'			=> 'AUTH_CAPTURE',
			'x_card_num'		=> $this->card_num,
			'x_exp_date'		=> $this->exp_date,
			'x_card_code'		=> $this->card_code,
			
			'x_email'			=> $order_data['address']['email'],
			'x_amount'			=> $order_data['checkout']['total'],
			
			'x_first_name'		=> $order_data['address']['bill_firstname'],
			'x_last_name'		=> $order_data['address']['bill_lastname'],
			'x_address'			=> $order_data['address']['bill_address'].$order_data['address']['bill_address2'],
			'x_country'			=> $country_code,
			'x_state'			=> $state_code,
			'x_city'			=> $order_data['address']['city'],
			'x_zip'				=> $order_data['address']['bill_postcode']
		);
		
		$content = '';
		foreach($data as $k=>$v){
			$content .= ($content ? '&' : '').$k.'='.urlencode($v);
		}
		
		$res = $this->send_request($content);
		if($res){
			$status = $this->model('payment')->pay_status('processing');
			$this->model('payment')->status = $status;
			
			//pay log
			$remark = 'authorization_code:'.$res[4].'; transaction_id:'.$res[6];
			$data = array(
				'type' => 1,
				'order_sn' => $order_data['order_sn'],
				'model' => 'authorize',
				'track_id' => $res[6],
				'currency' => $order_data['currency'],
				'money' => $order_data['checkout']['total'],
				'remark' => $remark,
				'time' => time()
			);
			$this->db->table('payment_log')->insert($data);
			
			$this->model('payment')->message = $res[3];
			return true;
		}else{
			return false;
		}
	}
	
	public function refund($trans_id, $order)
	{
		$data = array(
			'x_version'			=> '3.1',
			'x_delim_data'		=> 'TRUE',
			'x_delim_char'		=> $this->x_delim_char,
			'x_encap_char'		=> $this->x_encap_char,
			'x_relay_response'	=> 'FALSE',
			
			'x_login'			=> $this->api_id,
			'x_tran_key'		=> $this->api_key,
			
			'x_type'			=> 'VOID',
			'x_trans_id'		=> $trans_id,
		);
		
		$content = '';
		foreach($data as $k=>$v){
			$content .= ($content ? '&' : '').$k.'='.urlencode($v);
		}
		
		$res = $this->send_request($content);
		if($res){
			$status = $this->model('payment')->pay_status('refunded');
			$this->model('payment')->status = $status;
			
			//pay log
			$remark = 'Void:'.$res[3].'; transaction_id:'.$res[6];
			$data = array(
				'type' => 2,
				'order_sn' => $order['order_sn'],
				'model' => 'authorize',
				'track_id' => $res[6],
				'currency' => $order['currency'],
				'money' => $order['total_amount'],
				'remark' => $remark,
				'time' => time()
			);
			$this->db->table('payment_log')->insert($data);
			
			$this->model('payment')->message = $res[3];
			return true;
		}else{
			return false;
		}
	}
	
	private function send_request($content)
	{
		$response = $this->load('lib/http')->post($this->api_url, $content);
		
		$char = $this->x_encap_char.$this->x_delim_char.$this->x_encap_char;
		$rs = explode($char, substr($response, 1, -1));
		
		if (count($rs) < 10) {
			$this->model('report')->add('authorize', 'Unrecognized response from AuthorizeNet: '.$response);
			return false;
		}
		
		if($rs[0] != self::APPROVED){
			$this->model('payment')->error_msg = $rs[2].' '.$rs[3];
			$this->model('payment')->error_log(0, 'authorize');
			return null;
		}else{
			return $rs;
		}
	}
}
?>