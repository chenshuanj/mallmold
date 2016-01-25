<?php
/*
*	@checkout.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

class checkout extends model
{
	public function checkout_init()
	{
		return array(
			'subtotal' => 0,
			'tax' => 0,
			'shipping' => 0,
			'discount' => 0,
			'total' => 0,
			'coupon' => 0, //use coupon
			'gift' => 0,  //gift coupon
		);
	}
	
	public function shipping_list($country_id = false)
	{
		$where = 'status=1';
		if($country_id === 0){
			$where .= ' and country_id=0';
		}elseif($country_id > 0){
			$where .= " and country_id in(0,$country_id)";
		}
		return $this->db->table('shipping')->where($where)->getlist();
	}
	
	public function get_shipping_fee($shipping_id, $region_id=0, $weight=0)
	{
		if(!$shipping_id){
			return 0;
		}
		
		$shipping = $this->db->table('shipping')->where("shipping_id=$shipping_id")->get();
		$base_fee = $shipping['base_fee'];
		$step_fee = $shipping['step_fee'];
		if($region_id > 0){
			$row = $this->db->table('shipping_set')->where("shipping_id=$shipping_id and region_id=$region_id")->get();
			if($row){
				$base_fee = $row['base_fee'];
				$step_fee = $row['step_fee'];
			}
		}
		
		$shipping_fee = $base_fee;
		if($weight > $shipping['base_weight'] && $shipping['step_weight']){
			$over_weight = $weight - $shipping['base_weight'];
			$n = ceil($over_weight/$shipping['step_weight']);
			$shipping_fee += $step_fee*$n;
		}
		
		return $this->model('common')->current_price($shipping_fee, 0);
	}
	
	public function get_tax_rate($country_id, $region_id=0)
	{
		if(!$country_id){
			return 0;
		}
		
		$tax = $this->db->table('tax')->where("status=1 and country_id=$country_id")->get();
		if(!$tax){
			return 0;
		}
		
		$tax_rate = $tax['defaut_tax'];
		if($region_id > 0){
			$tax_id = $tax['id'];
			$row = $this->db->table('tax_set')->where("tax_id=$tax_id and region_id=$region_id")->get();
			if($row){
				$tax_rate = $row['tax'];
			}
		}
		return $tax_rate;
	}
	
	public function count_cart_total($country_id=0, $region_id=0, $shipping_id=0)
	{
		$checkout = $this->checkout_init();
		$checkout['subtotal'] = $this->model('cart')->get_total();
		
		if($shipping_id){
			$weight = $this->model('cart')->get_total_weight();
			$checkout['shipping'] = $this->get_shipping_fee($shipping_id, $region_id, $weight);
		}
		if($country_id){
			$tax_rate = $this->get_tax_rate($country_id, $region_id);
			$checkout['tax'] = round($checkout['subtotal']*($tax_rate/100), 2);
		}
		
		$tmp  = $this->get_checkout();
		if($tmp['coupon_id']){
			$checkout['coupon'] = $this->model('coupon')->get_money($tmp['coupon_id']);
		}
		
		$checkout['total'] = $checkout['subtotal']+$checkout['tax']+$checkout['shipping']-$checkout['coupon'];
		$checkout = $this->model('discount')->discount_count($checkout);
		return $checkout;
	}
	
	public function get_checkout()
	{
		$session_id = session_id();
		return $this->db->table('checkout')->where("session_id='$session_id'")->get();
	}
	
	public function save_checkout(array $data, $coupon_id=0)
	{
		$data = array_filter($data);
		if(!$data && !$coupon_id){
			return null;
		}
		
		$checkout = array(
			'session_id' => session_id(),
			'address_id' => isset($data['address_id']) ? intval($data['address_id']) : 0,
			'email' => isset($data['email']) ? trim($data['email']) : '',
			'firstname' => isset($data['firstname']) ? trim($data['firstname']) : '',
			'lastname' => isset($data['lastname']) ? trim($data['lastname']) : '',
			'country_id' => isset($data['country_id']) ? intval($data['country_id']) : 0,
			'region_id' => isset($data['region_id']) ? intval($data['region_id']) : 0,
			'city' => isset($data['city']) ? trim($data['city']) : '',
			'address' => isset($data['address']) ? trim($data['address']) : '',
			'address2' => isset($data['address2']) ? trim($data['address2']) : '',
			'phone' => isset($data['phone']) ? trim($data['phone']) : '',
			'postcode' => isset($data['postcode']) ? trim($data['postcode']) : '',
			
			'billtosame' => isset($data['billtosame']) ? intval($data['billtosame']) : 1,
			'bill_firstname' => isset($data['bill_firstname']) ? trim($data['bill_firstname']) : '',
			'bill_lastname' => isset($data['bill_lastname']) ? trim($data['bill_lastname']) : '',
			'bill_country_id' => isset($data['bill_country_id']) ? intval($data['bill_country_id']) : 0,
			'bill_region_id' => isset($data['bill_region_id']) ? intval($data['bill_region_id']) : 0,
			'bill_city' => isset($data['bill_city']) ? trim($data['bill_city']) : '',
			'bill_address' => isset($data['bill_address']) ? trim($data['bill_address']) : '',
			'bill_address2' => isset($data['bill_address2']) ? trim($data['bill_address2']) : '',
			'bill_phone' => isset($data['bill_phone']) ? trim($data['bill_phone']) : '',
			'bill_postcode' => isset($data['bill_postcode']) ? trim($data['bill_postcode']) : '',
			
			'shipping_id' => isset($data['shipping_id']) ? intval($data['shipping_id']) : 0,
			'payment_id' => isset($data['payment_id']) ? intval($data['payment_id']) : 0,
			'coupon_id' => intval($coupon_id),
			'time' => time(),
		);
		
		$row = $this->get_checkout();
		if($row){
			$id = $row['id'];
			$this->db->table('checkout')->where("id=$id")->update($checkout);
		}else{
			$this->db->table('checkout')->insert($checkout);
		}
		return true;
	}
	
	public function del_checkout()
	{
		$session_id = session_id();
		return $this->db->table('checkout')->where("session_id='$session_id'")->delete();
	}
	
	public function clear_checkout()
	{
		$time = time() - 24*3600;
		return $this->db->table('checkout')->where("time<$time")->delete();
	}
}
?>