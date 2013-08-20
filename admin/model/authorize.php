<?php
/*
*	@authorize.php (AIM)
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

class authorize extends model
{
	private $api_url= 'https://secure.authorize.net/gateway/transact.dll';
	private $api_id;
	private $api_key;
	private $refund = true;
	
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
		$this->api_id = $setting['api_id'];
		$this->api_key = $setting['api_key'];
	}
	
	public function can_refund()
	{
		return $this->refund;
	}
	
	public function validate($pay, $refund)
	{
		if($pay['money'] != $refund){
			$this->model('payment')->error_msg = 'The refund amount can not accept, must be: '.$pay['money'];
			return false;
		}else{
			return true;
		}
	}
	
	public function refund($pay, $order)
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
			'x_trans_id'		=> $pay['track_id'],
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
			$this->model('payment')->error_log($order['order_id'], 'authorize');
			return false;
		}
	}
	
	private function send_request($content)
	{
		$response = $this->load('lib/http')->post($this->api_url, $content);
		if($content === false){
			$this->model('authorize')->error_report('authorize', $this->load('lib/http')->error);
			return false;
		}
		
		$char = $this->x_encap_char.$this->x_delim_char.$this->x_encap_char;
		$rs = explode($char, substr($response, 1, -1));
		
		if (count($rs) < 10){
			$this->model('payment')->error_msg = 'authorize - Unrecognized response from AuthorizeNet: '.$response;
			return false;
		}
		
		if($rs[0] != self::APPROVED){
			$this->model('payment')->error_msg = $rs[2].' '.$rs[3];
			return false;
		}else{
			return $rs;
		}
	}
}
?>