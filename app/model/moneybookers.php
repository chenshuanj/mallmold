<?php
/*
*	@moneybookers.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

class moneybookers extends model
{
	private $test_mode = 0;
	private $env_url = 'https://www.moneybookers.com/app/payment.pl';
	private $env_url_test = 'https://www.moneybookers.com/app/test_payment.pl';
	private $data = array();
	private $localpay = false;
	private $repay = true;
	
	public $pay_to_email;
	public $secret;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_moneybookers')->get();
		$this->test_mode = $setting['test_mode'];
		$this->pay_to_email = $setting['pay_to_email'];
		$this->secret = $setting['secret'];
		
		if($this->test_mode == 1){
			$this->env_url = $this->env_url_test;
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
		if(!$this->pay_to_email){
			$this->model('payment')->error_msg = 'moneybookers pay_to_email is null';
			return false;
		}
		
		$pay_params = array(
			'pay_to_email'			=> $this->pay_to_email,
			'language'				=> $order['language'],
			'amount'				=> $order['total_amount'],
			'currency'				=> $order['currency'],
			'transaction_id'		=> $order['order_id'],
			'detail1_description'	=> 'Order #'.$order['order_sn'],
			'detail1_text'			=> 'Order #'.$order['order_sn'],
			'status_url'			=> htmlspecialchars(url('notify/notify_moneybookers')),
			'return_url'			=> htmlspecialchars(url('order/success?order_id='.$order['order_id'])),
			'cancel_url'			=> htmlspecialchars(url('account/orderview?order_id='.$order['order_id'])),
		);
		
		$this->data = $pay_params;
		return true;
	}
	
	public function verify()
	{
		$concatFields = $_POST['merchant_id'].$_POST['transaction_id']
						.strtoupper(md5($this->secret)).$_POST['mb_amount']
						.$_POST['mb_currency'].$_POST['status'];
		$status = false;
		if(strtoupper(md5($concatFields)) == $_POST['md5sig'] && $_POST['pay_to_email'] == $this->pay_to_email){
			$status = true;
		}else{
			$this->model('payment')->error_msg = 'Verification failed: '.var_export($_POST, true);
			$this->model('payment')->error_log($_POST['transaction_id'], 'moneybookers');
		}
		return $status;
	}
	
	public function get_form()
	{
		return array(
			'action' => $this->env_url,
			'data' => $this->data,
		);
	}
}
?>