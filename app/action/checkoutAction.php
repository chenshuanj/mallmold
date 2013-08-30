<?php
/*
*	@checkoutAction.php
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

class checkoutAction extends commonAction
{
	public function index()
	{
		$is_login = $this->model('user')->is_login();
		//Allow visitors to order
		if(!$is_login){
			$setting = &$this->model('common')->setting();
			if($setting['visitor_order'] != 1){
				header('Location: '.url('user/login'));
			}
		}
		
		$cart_num = $this->model('cart')->get_num();
		if(!$cart_num){
			$this->error('Shopping cart is empty');
			return;
		}
		
		//Temporarily saved address
		$checkout  = $this->model('checkout')->get_checkout();
		$address_id = intval($checkout['address_id']);
		if($address_id > 0){
			$address = $this->model('address')->get_address($address_id);
			$checkout['country_id'] = $address['country_id'];
			$checkout['region_id'] = $address['region_id'];
		}
		$country_id = intval($checkout['country_id']);
		$shipping_id = intval($checkout['shipping_id']);
		$region_id = intval($checkout['region_id']);
		
		//host bind country_id
		if(!$country_id){
			$set = $this->model('visitor')->visitor_set();
			if($set['bind_country']){
				$country_id = $set['bind_country'];
			}
		}
		
		//My saved address
		if($is_login){
			$this->view['address_list'] = $this->model('address')->address_list();
			if(!$checkout['email']){
				$user = $this->model('user')->get();
				$checkout['email'] = $user['email'];
				if(!$checkout['firstname']){
					$checkout['firstname'] = $user['firstname'];
					$checkout['lastname'] = $user['lastname'];
				}
			}
		}
		
		$region_list = array();
		if($country_id > 0){
			$region_list = $this->model('region')->region_list($country_id);
		}
		
		$this->view['address_id'] = $address_id;
		$this->view['address_tmp'] = $checkout;
		$this->view['is_login'] = $is_login;
		$this->view['country_list'] = $this->model('region')->country_list();
		$this->view['region_list'] = $region_list;
		
		$this->view['shipping_list'] = $this->model('checkout')->shipping_list($country_id);
		$this->view['payment_list'] = $this->model('payment')->payment_list($country_id);
		$this->view['checkout'] = $this->model('checkout')->count_cart_total($country_id, $region_id, $shipping_id);
		
		$this->view['html_title'] = lang('Checkout');
		$this->view('checkout/index.html');
	}
	
	public function place_order()
	{
		$this->model('checkout')->save_checkout($_POST);
		$is_login = $this->model('user')->is_login();
		$cart_num = $this->model('cart')->get_num();
		if(!$cart_num){
			$this->error('Shopping cart is empty');
			return;
		}
		
		$email = trim($_POST['email']);
		if(!$email){
			$this->error('Email not filled');
			return;
		}
		
		$address_id = intval($_POST['address_id']);
		if(!$address_id){
			$country_id = intval($_POST['country_id']);
			$region_id = intval($_POST['region_id']);
			$firstname = trim($_POST['firstname']);
			$lastname = trim($_POST['lastname']);
			$address = trim($_POST['address']);
			$address2 = trim($_POST['address2']);
			$city = trim($_POST['city']);
			$postcode = trim($_POST['postcode']);
			$phone = trim($_POST['phone']);
			
			if(!$country_id || !$region_id){
				$this->error('Please select a country or region');
				return;
			}
			if(!$firstname || !$lastname){
				$this->error('Please fill in your name');
				return;
			}
			if(!$address || !$city){
				$this->error('Please fill in your address or city');
				return;
			}
			if(!$postcode || !$phone){
				$this->error('Please fill in your Zip code or phone number');
				return;
			}
			
			//billing address
			$billtosame = intval($_POST['billtosame']);
			if($billtosame == 0){
				$bill_country_id = intval($_POST['bill_country_id']);
				$bill_region_id = intval($_POST['bill_region_id']);
				$bill_firstname = trim($_POST['bill_firstname']);
				$bill_lastname = trim($_POST['bill_lastname']);
				$bill_address = trim($_POST['bill_address']);
				$bill_address2 = trim($_POST['bill_address2']);
				$bill_city = trim($_POST['bill_city']);
				$bill_postcode = trim($_POST['bill_postcode']);
				$bill_phone = trim($_POST['bill_phone']);
				
				if(!$bill_country_id || !$bill_region_id){
					$this->error('Please select a billing country or region');
					return;
				}
				if(!$bill_firstname || !$bill_lastname){
					$this->error('Please fill in your billing name');
					return;
				}
				if(!$bill_address || !$bill_city){
					$this->error('Please fill in your billing address or city');
					return;
				}
				if(!$bill_postcode || !$bill_phone){
					$this->error('Please fill in your billing Zip code or phone number');
					return;
				}
			}else{
				$bill_country_id = $country_id;
				$bill_region_id = $region_id;
				$bill_firstname = $firstname;
				$bill_lastname = $lastname;
				$bill_address = $address;
				$bill_address2 = $address2;
				$bill_city = $city;
				$bill_postcode = $postcode;
				$bill_phone = $phone;
			}
			
			//create account
			if(!$is_login && $_POST['newaccount']==1){
				if($_POST['password'] && $_POST['password']==$_POST['repassword']){
					$this->model('user')->register($email, $_POST['password'], $firstname, $lastname);
					$is_login = $this->model('user')->is_login();
				}else{
					$this->error('Incorrect password authentication');
					return;
				}
			}
		
			$user_id = $is_login ? intval($_SESSION['user_id']) : 0;
			$order_address = array(
				'user_id' => $user_id,
				'email' => $email,
				'firstname' => $firstname,
				'lastname' => $lastname,
				'country_id' => $country_id,
				'region_id' => $region_id,
				'city' => $city,
				'address' => $address,
				'address2' => $address2,
				'phone' => $phone,
				'postcode' => $postcode,
				'bill_firstname' => $bill_firstname,
				'bill_lastname' => $bill_lastname,
				'bill_country_id' => $bill_country_id,
				'bill_region_id' => $bill_region_id,
				'bill_city' => $bill_city,
				'bill_address' => $bill_address,
				'bill_address2' => $bill_address2,
				'bill_phone' => $bill_phone,
				'bill_postcode' => $bill_postcode,
			);
		}else{
			$order_address = $this->model('address')->get_address($address_id);
			$order_address['email'] = $email;
			$country_id = $order_address['country_id'];
		}
		
		//save address
		if($is_login && $_POST['saveaddress']==1){
			$this->model('address')->save_address($order_address);
		}
		
		$shipping_id = intval($_POST['shipping_id']);
		$payment_id = intval($_POST['payment_id']);
		if(!$shipping_id){
			$this->error('Please select a shipping method');
			return;
		}
		if(!$payment_id){
			$this->error('Please select a payment method');
			return;
		}
		
		//check payment
		$payment = $this->model('payment')->get_payment($payment_id, $country_id);
		if(!$payment){
			$this->error('Payment is invalid');
			return;
		}
		
		//Validate payment data
		$check = $this->model('payment')->validate($payment);
		if(!$check){
			$this->error(lang('Failed to validate: '.$this->model('payment')->error_msg));
			return;
		}
		
		$order_data = array(
			'cart_list' => $this->model('cart')->getlist(),
			'checkout' => $this->model('checkout')->count_cart_total($order_address['country_id'], $order_address['region_id'], $shipping_id),
			'currency' => $this->model('common')->current_cur(),
			'payment' => $payment,
			'address' => $order_address,
			'shipping_id' => $shipping_id,
			'order_sn' => $this->model('order')->get_sn(),
		);
		
		//local pay
		$status = $this->model('payment')->localpay($order_data);
		if($status == -1){
			$url = url('checkout/index');
			$this->error('Failed to pay: '.$this->model('payment')->error_msg, $url);
			return;
		}
		
		//create
		$order_id = $this->model('order')->creat($order_data, $status);
		if(!$order_id){
			$this->error('Failed to create order');
			return;
		}else{
			$this->model('cart')->truncate();
			$this->model('checkout')->del_checkout();
		}
		
		if($status == 1){
			$order = $this->model('order')->order_get($order_id);
			$this->model('payment')->pay_success($order);
			
			$url = url('order/success?order_id='.$order_id);
		}else{
			$url = url('order/pay?order_id='.$order_id);
		}
		
		$_SESSION['last_order'] = $order_id;
		header('Location: '.$url);
	}
	
	public function ajax_update_country()
	{
		$this->model('checkout')->save_checkout($_POST);
		
		$address_id = intval($_POST['address_id']);
		if($address_id > 0){
			$address = $this->model('address')->get_address($address_id);
			$country_id = $address['country_id'];
			$region_id = $address['region_id'];
			$region_html = $this->model('region')->ajax_options(0);
		}else{
			$country_id = intval($_POST['country_id']);
			$region_html = $this->model('region')->ajax_options($country_id);
		}
		
		$this->view['shipping_list'] = $this->model('checkout')->shipping_list($country_id);
		$this->view['payment_list'] = $this->model('payment')->payment_list($country_id);
		$this->view['checkout'] = $this->model('checkout')->count_cart_total($country_id, $region_id);
		
		$return_data = array(
			'region_html' => $region_html,
			'shipping_method' => $this->view('checkout/shipping_method.html', 0),
			'payment_method' => $this->view('checkout/payment_method.html', 0),
			'pay_details' => $this->view('checkout/pay_details.html', 0),
		);
		
		header('Content-type: text/html; charset=utf-8');
		echo json_encode($return_data);
	}
	
	public function ajax_update_shipping()
	{
		$this->model('checkout')->save_checkout($_POST);
		
		$shipping_id = intval($_POST['shipping_id']);
		$address_id = intval($_POST['address_id']);
		if($address_id > 0){
			$address = $this->model('address')->get_address($address_id);
			$country_id = $address['country_id'];
			$region_id = $address['region_id'];
		}else{
			$country_id = intval($_POST['country_id']);
			$region_id = intval($_POST['region_id']);
		}
		
		$this->view['checkout'] = $this->model('checkout')->count_cart_total($country_id, $region_id, $shipping_id);
		
		$return_data = array(
			'pay_details' => $this->view('checkout/pay_details.html', 0),
		);
		header('Content-type: text/html; charset=utf-8');
		echo json_encode($return_data);
	}
	
	public function ajax_zipfindcity()
	{
		$country_id = intval($_POST['country_id']);
		$postcode = trim($_POST['postcode']);
		$return_data = array('status' => 0);
		if($country_id>0 && $postcode && !preg_match("/[^0-9a-zA-Z\s]/", $postcode)){
			$code = $this->db->table('country')->where("id=$country_id")->getval('code');
			if($code){
				$table = 'region_city_'.strtolower($code);
				$row = $this->db->table($table)->where("postcode='$postcode'")->get();
				if($row){
					$return_data['status'] = 1;
					$return_data['city'] = $row['name'];
					$return_data['region_id'] = $row['region_id'];
				}
			}
		}
		header('Content-type: text/html; charset=utf-8');
		echo json_encode($return_data);
	}
	
	public function ajax_bill_country()
	{
		$bill_country_id = intval($_POST['bill_country_id']);
		$region_html = $this->model('region')->ajax_options($bill_country_id);
		$return_data = array(
			'region_html' => $region_html,
		);
		header('Content-type: text/html; charset=utf-8');
		echo json_encode($return_data);
	}
}

?>