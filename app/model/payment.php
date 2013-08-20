<?php
/*
*	@payment.php
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

class payment extends model
{
	public $status = 0;
	public $error_msg = '';
	public $message;
	
	public function pay_status($code)
	{
		$status = array(
			'pending_payment' => 0,
			'payment_review' => 1,
			'cancelled' => 2,
			'processing' => 3,
			'refunded' => 4,
			'complete' => 5,
		);
		return isset($status[$code]) ? $status[$code] : 0;
	}
	
	public function payment_list($country_id = false)
	{
		$where = 'status=1';
		if($country_id === 0){
			$where .= ' and bind=0';
		}elseif($country_id > 0){
			$where .= " and (bind=0 or (bind=1 and id in (
							select payment_id from ".$this->db->tbname('payment_bind')." 
							where country_id=$country_id
								)
							)
						)";
		}
		return $this->db->table('payment')->where($where)->getlist();
	}
	
	public function get_payment($payment_id, $country_id = false)
	{
		$payment = null;
		
		if($payment_id < 1){
			return $payment;
		}
		
		$payment_list = $this->payment_list($country_id);
		foreach($payment_list as $v){
			if($v['id'] == $payment_id){
				$payment = $v;
				break;
			}
		}
		
		return $payment;
	}
	
	public function can_repay($payment_id)
	{
		$model = $this->db->table('payment')->where("id=$payment_id")->getval('model');
		return $this->model($model)->can_repay();
	}
	
	public function validate($payment)
	{
		$model = $payment['model'];
		return $this->model($model)->validate();
	}
	
	//return: -1 Failed, 0 can't localpay(Pending Payment), 1 pay success(Payment review)
	public function localpay($order_data)
	{
		$model = $order_data['payment']['model'];
		$allow = $this->model($model)->can_localpay();
		if(!$allow){
			$this->status = $this->pay_status('pending_payment');
			return 0;
		}
		
		//localpay
		$status = $this->model($model)->localpay($order_data);
		if($status){
			return 1;
		}else{
			return -1;
		}
	}
	
	public function pay_success($order)
    {
		$setting = &$this->model('common')->setting();
		
		//update order status
		$this->model('order')->update_status($order['order_id'], $this->model('payment')->status);
		
		//add order history
		$data = array(
			'order_id' => $order['order_id'],
			'status' => $this->model('payment')->status,
			'remark' => $this->message,
			'notice' => intval($setting['user_order_notice']),
			'time' => time()
		);
		$this->db->table('order_status')->insert($data);
		
		//add goods sold_num
		foreach($order['goods'] as $goods){
			$this->model('statistic')->add($goods['goods_id'], 'buy');
		}
		
		//send gift
		if($order['gift'] > 0){
			$gift_id = $this->model('coupon')->creat($order['gift'], $order['email'], $order['order_id']);
			$this->model('event')->add('coupon.send', $gift_id);
		}
		
		//custemor notice
		if($setting['user_order_notice'] == 1){
			$this->model('event')->add('order.pay', $order['order_id']);
		}
		
		//admin notice
		if($setting['admin_order_notice'] == 1){
			$this->model('event')->add('order.notice', $order['order_id']);
		}
	}
	
	public function error_log($order_id, $method)
	{
		$data = array(
			'order_id' => intval($order_id),
			'method' => $method,
			'error_msg' => ($this->error_msg ? addslashes($this->error_msg) : 'Unknown'),
			'time' => time()
		);
		$error_id = $this->db->table('payment_error')->insert($data);
		
		//email notice
		if($this->config['admin_error_notice'] == 1){
			$this->model('event')->add('payment_error', $error_id);
		}
		
		return null;
	}
}
?>