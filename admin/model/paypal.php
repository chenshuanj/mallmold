<?php
/*
*	@paypal.php
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

class paypal extends model
{
	private $env_url = 'https://api-3t.sandbox.paypal.com/nvp';
	private $refund = true;
	
	private $api_username;
	private $api_password;
	private $api_signature;
	
	public $type = 'Full';
	public $amt = 0;
	public $note;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_paypal')->get();
		$this->api_username = $setting['api_username'];
		$this->api_password = $setting['api_password'];
		$this->api_signature = $setting['api_signature'];
	}
	
	public function can_refund()
	{
		return $this->refund;
	}
	
	public function validate($pay, $refund)
	{
		if($refund <=0 || $refund > $pay['money']){
			$this->model('payment')->error_msg = 'The refund amount can not accept, must be: 0.00 - '.$pay['money'];
			return false;
		}
		
		$this->amt = $refund;
		if($refund < $pay['money']){
			$this->type = 'Partial';
			$this->note = 'Refund';
		}
		
		return true;
	}
	
	public function refund($pay, $order)
	{
		$params = array(
			'VERSION'				=> '65.1',
			'PWD'					=> $this->api_password,
			'USER'					=> $this->api_username,
			'SIGNATURE'				=> $this->api_signature,
			'TRANSACTIONID'			=> $pay['track_id'],
			'REFUNDTYPE'			=> $this->type, //Full or Partial
			'CURRENCYCODE'			=> $pay["currency"],
			'NOTE'					=> $this->note,
		);
		
		if($this->type == 'Partial'){
			$params['AMT'] = $this->amt;
		}
		
		$req = 'METHOD=RefundTransaction';
		foreach($params as $key => $value){
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		
		$status = false;
		$content = $this->load('lib/http')->post($this->env_url, $req);
		if($content === false){
			$this->model('payment')->error_report('paypal', $this->load('lib/http')->error);
		}else{
			parse_str($content, $res);
			if($res['ACK'] == 'SUCCESS'){
				$status = $this->model('payment')->pay_status('refunded');
				$this->model('payment')->status = $status;
				
				//pay log
				$remark = 'FEEREFUNDAMT:'.$res['FEEREFUNDAMT'].'; transaction_id:'.$res['BUILD'];
				$data = array(
					'type' => 2,
					'order_sn' => $order['order_sn'],
					'model' => 'paypal',
					'track_id' => $res['CORRELATIONID'],
					'currency' => $pay['currency'],
					'money' => $this->amt,
					'remark' => $remark,
					'time' => time()
				);
				$this->db->table('payment_log')->insert($data);
			
				$this->model('payment')->message = $res['L_LONGMESSAGE0'];
				$status = true;
			}else{
				$this->model('payment')->error_msg = $res['L_ERRORCODE0'].' '.$res['L_LONGMESSAGE0'];
				$this->model('payment')->error_log($order['order_id'], 'paypal');
			}
		}
		return $status;
	}

}
?>