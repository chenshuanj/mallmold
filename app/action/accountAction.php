<?php
/*
*	@accountAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require Action('common');

class accountAction extends commonAction
{
	private function check_login()
	{
		if(!$this->model('user')->is_login()){
			header('Location: '.url('user/login'));
			return false;
		}
		return true;
	}
	
	public function index()
	{
		if(!$this->check_login()){
			return;
		}
		
		$user = $this->model('user')->get($_SESSION['user_id']);
		$this->view['user'] = $user;
		$this->view['group'] = $this->model('user')->get_group($user['group_id']);
		$this->view['html_title'] = lang('My account');
		$this->view('account/index.html');
	}
	
	public function chang_pswd()
	{
		if(!$this->check_login()){
			return;
		}
		
		$user = $this->model('user')->get($_SESSION['user_id']);
		
		if($_POST['submit']){
			$password = $_POST['password'];
			$newpassword = $_POST['newpassword'];
			$repassword = $_POST['repassword'];
			
			if($this->model('user')->encrypt($password, $user['salt']) != $user['password']){
				$this->error('Wrong password');
				return;
			}
			if(strlen($newpassword)<6){
				$this->error('Password length must be greater than 5');
				return;
			}
			if($repassword != $newpassword){
				$this->error('Confirm password do not match');
				return;
			}
			
			$this->model('user')->change_pswd($newpassword);
			$this->view['have_change'] = 1;
		}
		
		$this->view['user'] = $user;
		$this->view['html_title'] = lang('Change Password');
		$this->view('account/chang_pswd.html');
	}
	
	public function address()
	{
		if(!$this->check_login()){
			return;
		}
		
		$this->view['address_list'] = $this->model('address')->address_list();
		$this->view['html_title'] = lang('My address');
		$this->view('account/address.html');
	}
	
	public function add_address()
	{
		if(!$this->check_login()){
			return;
		}
		
		if($_POST['submit']){
			$id = intval($_POST['id']);
			unset($_POST['id']);
			$status = $this->model('address')->save_address($_POST, $id);
			if($status){
				$this->view['have_save'] = 1;
			}else{
				$this->view['have_save'] = -1;
				$this->view['address'] = $_POST;
				if($_POST['country_id']){
					$this->view['region_list'] = $this->model('region')->region_list($_POST['country_id']);
				}
				if($_POST['bill_country_id']){
					$this->view['bill_region_list'] = $this->model('region')->region_list($_POST['bill_country_id']);
				}
			}
		}else{
			$id = intval($_GET['id']);
		}
		
		if($id){
			$address = $this->model('address')->get_address($id);
			$region_list = $this->model('region')->region_list($address['country_id']);
			$this->view['address'] = $address;
			$this->view['region_list'] = $region_list;
			$this->view['bill_region_list'] = $this->model('region')->region_list($address['bill_country_id']);
		}
		
		$this->view['country_list'] = $this->model('region')->country_list();
		$this->view['html_title'] = lang('Edit address');
		$this->view('account/add_address.html');
	}
	
	public function del_address()
	{
		if(!$this->check_login()){
			return;
		}
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('Parameter error');
			return;
		}
		
		$address = $this->model('address')->get_address($id);
		if($address['user_id'] != $_SESSION['user_id']){
			$this->error('Parameter error');
			return;
		}
		
		$this->model('address')->del_address($id);
		header('Location: '.url('account/address'));
	}
	
	public function ajax_region_list()
	{
		$country_id = intval($_POST['country_id']);
		echo $this->model('region')->ajax_options($country_id);
	}
	
	public function order()
	{
		if(!$this->check_login()){
			return;
		}
		
		$user_id = $_SESSION['user_id'];
		
		$symbols = array();
		$currencies = &$this->model('common')->currencies();
		foreach($currencies as $v){
			$symbols[$v['code']] = $v['symbol'];
		}
		
		$this->view['symbols'] = $symbols;
		$this->view['order_status'] = $this->model('order')->order_status();
		$this->view['order_list'] = $this->model('order')->order_list($user_id);
		
		$this->view['html_title'] = lang('My order');
		$this->view('account/order.html');
	}
	
	public function orderview()
	{
		if(!$this->check_login()){
			return;
		}
		$order_id = intval($_GET['order_id']);
		if(!$order_id){
			$this->error('Parameter error');
			return;
		}
		
		$order = $this->model('order')->order_get($order_id);
		
		//if is my order
		if($order['user_id'] != $_SESSION['user_id']){
			$this->error('Invalid parameter');
			return;
		}
		
		$this->view['order'] = $order;
		$this->view['order_status'] = $this->model('order')->order_status();
		$this->view['html_title'] = lang('My order');
		$this->view('account/order_view.html');
	}
}

?>