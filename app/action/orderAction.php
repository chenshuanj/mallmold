<?php
/*
*	@orderAction.php
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

require Action('common');

class orderAction extends commonAction
{
	public function pay()
	{
		$order_id = intval($_GET['order_id']);
		if(!$order_id){
			$this->error('Parameter error');
			return;
		}
		
		$order = $this->model('order')->order_get($order_id);
		
		//status
		if($order['status'] != 0){
			$this->error('Wrong order status');
			return;
		}
		
		//payment
		$payment = $this->model('payment')->get_payment($order['payment_id'], $order['shipping_address']['country_id']);
		if(!$payment){
			$this->error('Payment is invalid');
			return;
		}
		
		$_SESSION['last_order'] = $order_id;
		$model = $payment['model'];
		return $this->$model($order);
	}
	
	private function paypal($order)
	{
		$status = $this->model('paypal')->set_pay_params($order);
		if(!$status){
			$this->model('payment')->error_log($order['order_id'], 'paypal');
			$this->error($this->model('payment')->error_msg);
			return;
		}
		
		$this->view['data'] = $this->model('paypal')->get_form();
		$this->view['html_title'] = 'Redirect to Paypal';
		$this->view('checkout/paypal.html');
	}
	
	private function authorize($order)
	{
		$this->error('Payment is invalid');
		return;
	}
	
	public function success()
	{
		$order_id = intval($_GET['order_id']);
		if(!$order_id){
			$this->error('Parameter error');
			return;
		}
		
		if($order_id != $_SESSION['last_order']){
			header('Location: '.url('account/orderview?order_id='.$order_id));
			return;
		}
		
		$_SESSION['last_order'] = null;
		
		$order = $this->model('order')->order_get($order_id);
		if(!$order){
			$this->error('Parameter error');
			return;
		}
		
		//status
		if($order['status'] == 0){
			header('Location: '.url('account/orderview?order_id='.$order_id));
			return;
		}
		
		$this->view['order'] = $order;
		$this->view['html_title'] = 'Order success';
		$this->view('checkout/success.html');
	}
}
?>