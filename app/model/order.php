<?php
/*
*	@order.php
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

class order extends model
{
	public function order_status()
	{
		return array(
			0 => lang('Pending Payment'),
			1 => lang('Payment review'),
			2 => lang('Cancelled'),
			3 => lang('Processing'),
			4 => lang('Refunded'),
			5 => lang('Complete'),
		);
	}
	
	public function shipping_status()
	{
		return array(
			0 => lang('Unshipped'),
			1 => lang('Partial shipped'),
			2 => lang('All shipped'),
		);
	}
	
	public function creat(array $order_data, $status)
	{
		$order_sn = $order_data['order_sn'];
		$address = $order_data['address'];
		$checkout = $order_data['checkout'];
		$payment_id = $order_data['payment']['id'];
		$language = $this->config['LAN_NAME'];
		
		$coupon_id = 0;
		if($checkout['coupon'] > 0){
			$tmp = $this->model('checkout')->get_checkout();
			$coupon_id = $tmp['coupon_id'];
		}
		
		$data = array(
			'order_sn' => $order_sn,
			'user_id' => $address['user_id'],
			'email' => $address['email'],
			'shipping_id' => $order_data['shipping_id'],
			'payment_id' => $payment_id,
			'coupon_id' => $coupon_id,
			'currency' => $order_data['currency'],
			'language' => $language,
			'goods_amount' => $checkout['subtotal'],
			'shipping_fee' => $checkout['shipping'],
			'tax_fee' => $checkout['tax'],
			'total_amount' => $checkout['total'],
			'gift' => $checkout['gift'],
			'addtime' => time(),
			'status' => $status,
		);
		$order_id = $this->db->table('order')->insert($data);
		if(!$order_id){
			return 0;
		}
		
		//save goods
		foreach($order_data['cart_list'] as $v){
			$goods_data = array(
				'order_id' => $order_id,
				'goods_id' => $v['goods_id'],
				'goods_name' => addslashes($v['goods']['title']),
				'options' => addslashes(json_encode($v['options_name'])),
				'price' => $v['subtotal']/$v['quantity'],
				'quantity' => $v['quantity'],
				'subtotal' => $v['subtotal'],
				'shipping' => 0,
			);
			$this->db->table('order_goods')->insert($goods_data);
			$this->model('statistic')->add($v['goods_id'], 'buy');
		}
		
		//save shipping address
		$shipping_address = array(
			'order_id' => $order_id,
			'user_id' => $address['user_id'],
			'firstname' => $address['firstname'],
			'lastname' => $address['lastname'],
			'country_id' => $address['country_id'],
			'region_id' => $address['region_id'],
			'city' => $address['city'],
			'address' => $address['address'],
			'address2' => $address['address2'],
			'phone' => $address['phone'],
			'postcode' => $address['postcode'],
		);
		$this->db->table('order_shipping_address')->insert($shipping_address);
		
		//save billing address
		$billing_address = array(
			'order_id' => $order_id,
			'user_id' => $address['user_id'],
			'firstname' => $address['bill_firstname'],
			'lastname' => $address['bill_lastname'],
			'country_id' => $address['bill_country_id'],
			'region_id' => $address['bill_region_id'],
			'city' => $address['bill_city'],
			'address' => $address['bill_address'],
			'address2' => $address['bill_address2'],
			'phone' => $address['bill_phone'],
			'postcode' => $address['bill_postcode'],
		);
		$this->db->table('order_billing_address')->insert($billing_address);
		
		//set coupon used
		if($coupon_id > 0){
			$this->model('coupon')->set_used($coupon_id);
		}
		
		//notice
		$setting = &$this->model('common')->setting();
		if($setting['user_order_notice'] == 1){
			$this->model('event')->add('order.creat', $order_id);
		}
		
		//add order history
		$data = array(
			'order_id' => $order_id,
			'status' => 0,
			'remark' => 'Creat order',
			'notice' => intval($setting['user_order_notice']),
			'time' => time()
		);
		$this->db->table('order_status')->insert($data);
		
		return $order_id;
	}
	
	public function get_sn()
	{
		$setting = &$this->model('common')->setting();
		$order_prefix = $setting['order_prefix'];
		
		$sn = $this->db->table('order_sn')->getval('sn');
		$sn++;
		$this->db->table('order_sn')->update(array('sn' => $sn));
		return $order_prefix.$sn;
	}
	
	public function order_list($user_id)
	{
		if(!$user_id){
			return null;
		}
		
		$order_list = $this->db->table('order')->where("user_id=$user_id")->order('addtime desc')->getlist();
		foreach($order_list as $k=>$v){
			$order_list[$k]['time'] = $this->model('common')->date_format($v['addtime']);
			$order_list[$k]['address'] = $this->db->table('order_shipping_address')->where("order_id=".$v['order_id'])->get();
			
			if($v['status']==0){
				$can_repay = $this->model('payment')->can_repay($v['payment_id']);
				$order_list[$k]['can_repay'] = ($can_repay ? 1 : 0);
			}else{
				$order_list[$k]['can_repay'] = 0;
			}
		}
		
		return $order_list;
	}
	
	public function order_get($order_id)
	{
		$order = $this->db->table('order')->where("order_id=$order_id")->get();
		if(!$order){
			return false;
		}
		
		$code = $order['currency'];
		$order['time'] = $this->model('common')->date_format($order['addtime']);
		$order['symbol'] = $this->db->table('currency')->where("code='$code'")->getval('symbol');
		$order['shipping_method'] = $this->db->table('shipping')->where("shipping_id=".$order['shipping_id'])->getval('name');
		
		if($order['payment_id'] > 0){
			$order['payment_method'] = $this->db->table('payment')->where("id=".$order['payment_id'])->getval('name');
		}else{
			$order['payment_method'] = lang('Do not need pay');
		}
		
		$shipping_address = $this->db->table('order_shipping_address')->where("order_id=$order_id")->get();
		$shipping_address['country'] = $this->db->table('country')->where("id=".$shipping_address['country_id'])->getval('name');
		$shipping_address['state'] = $this->db->table('region')->where("region_id=".$shipping_address['region_id'])->getval('name');
		
		$billing_address = $this->db->table('order_billing_address')->where("order_id=$order_id")->get();
		$billing_address['country'] = $this->db->table('country')->where("id=".$billing_address['country_id'])->getval('name');
		$billing_address['state'] = $this->db->table('region')->where("region_id=".$billing_address['region_id'])->getval('name');
		
		$order['shipping_address'] = $shipping_address;
		$order['billing_address'] = $billing_address;
		$order['goods'] = $this->db->table('order_goods')->where("order_id=$order_id")->getlist();
		
		$status_list = $this->db->table('order_status')->where("order_id=$order_id")->order('time desc')->getlist();
		foreach($status_list as $k=>$v){
			$status_list[$k]['time'] = $this->model('common')->date_format($v['time']);
		}
		$order['status_list'] = $status_list;
		return $order;
	}
	
	public function update_status($order_id, $status)
	{
		return $this->db->table('order')->where("order_id=$order_id")->update(array('status' => $status));
	}
}
?>