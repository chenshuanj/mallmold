<?php
/*
*	@paymentAction.php
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

class paymentAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->db->table('payment')->getlist();
		$this->view['title'] = lang('payment');
		$this->view('payment/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->db->table('payment')->where("id=$id")->get();
				
				$model = 'payment/'.$data['model'].'.html';
				$table = 'payment_'.$data['model'];
				$setting = $this->db->table($table)->get();
				
				$binds = array();
				$bind_list = $this->db->table('payment_bind')->where("payment_id=$id")->getlist();
				foreach($bind_list as $v){
					$binds[] = $v['country_id'];
				}
				
				$this->view['data'] = $data;
				$this->view['model'] = $model;
				$this->view['setting'] = $setting;
				$this->view['binds'] = $binds;
			}
			$this->view['country_list'] = $this->db->table('country')->where('status=1')->getlist();
			$this->view('payment/edit.html');
		}else{
			if(!$_POST['name'] || !$_POST['id']){
				$this->error('required_null');
			}
			
			$data = array(
				'name' => trim($_POST['name']),
				'sort_order' => intval($_POST['sort_order']),
				'bind' => intval($_POST['bind']),
				'status' => intval($_POST['status']),
			);
			
			$id = intval($_POST['id']);
			$this->db->table('payment')->where("id=$id")->update($data);
			
			$model = $this->db->table('payment')->where("id=$id")->getval('model');
			$save_action = 'save_'.$model;
			$this->$save_action();
			
			if($data['bind'] == 1){
				$country_ids = $_POST['country_id'];
				if($country_ids){
					$this->db->table('payment_bind')->where("payment_id=$id")->delete();
					foreach($country_ids as $country_id){
						$this->db->table('payment_bind')->insert(array('payment_id'=>$id, 'country_id'=>$country_id));
					}
				}
			}
			
			$this->ok('edit_success', url('payment/index'));
		}
	}
	
	private function save_paypal()
	{
		$cert_file_dir = BASE_PATH .'/cert/paypal/';
		if(!is_dir($cert_file_dir)){
			@mkdir($cert_file_dir);
		}
		
		$setting = $this->db->table('payment_paypal')->get();
		$setting['test_mode'] = intval($_POST['test_mode']);
		$setting['email'] = trim($_POST['email']);
		$setting['type'] = intval($_POST['type']);
		$setting['paypal_cert_id'] = trim($_POST['paypal_cert_id']);
		
		if($_FILES['paypal_cert_file']['name']){
			$file_name = $_FILES['paypal_cert_file']['name'];
			$tmp_name = $_FILES['paypal_cert_file']['tmp_name'];
			$extension = strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
			if($extension == 'pem'){
				move_uploaded_file($tmp_name, $cert_file_dir.$file_name) 
				&& $setting['paypal_cert_file'] = $file_name;
			}
		}
		if($_FILES['my_public_cert_file']['name']){
			$file_name = $_FILES['my_public_cert_file']['name'];
			$tmp_name = $_FILES['my_public_cert_file']['tmp_name'];
			$extension = strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
			if($extension == 'txt'){
				move_uploaded_file($tmp_name, $cert_file_dir.$file_name) 
				&& $setting['my_public_cert_file'] = $file_name;
			}
		}
		if($_FILES['my_private_key_file']['name']){
			$file_name = $_FILES['my_private_key_file']['name'];
			$tmp_name = $_FILES['my_private_key_file']['tmp_name'];
			$extension = strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
			if($extension == 'pem'){
				move_uploaded_file($tmp_name, $cert_file_dir.$file_name) 
				&& $setting['my_private_key_file'] = $file_name;
			}
		}
		if($_POST['my_private_key_pswd']){
			$setting['my_private_key_pswd'] = trim($_POST['my_private_key_pswd']);
		}
		$this->db->table('payment_paypal')->update($setting);
	}
	
	private function save_authorize()
	{
		$setting = $this->db->table('payment_authorize')->get();
		$setting['test_mode'] = intval($_POST['test_mode']);
		$setting['api_id'] = trim($_POST['api_id']);
		$setting['api_key'] = trim($_POST['api_key']);
		$this->db->table('payment_authorize')->update($setting);
	}
	
	private function save_moneybookers()
	{
		$setting = $this->db->table('payment_moneybookers')->get();
		$setting['test_mode'] = intval($_POST['test_mode']);
		$setting['pay_to_email'] = trim($_POST['pay_to_email']);
		$setting['secret'] = trim($_POST['secret']);
		$this->db->table('payment_moneybookers')->update($setting);
	}
	
	private function save_paypal_pro()
	{
		$setting = $this->db->table('payment_paypal_pro')->get();
		$setting['test_mode'] = intval($_POST['test_mode']);
		$setting['user'] = trim($_POST['user']);
		$setting['password'] = trim($_POST['password']);
		$setting['signature'] = trim($_POST['signature']);
		$this->db->table('payment_paypal_pro')->update($setting);
	}
	
	private function save_paypal_express()
	{
		$setting = $this->db->table('payment_paypal_express')->get();
		$setting['test_mode'] = intval($_POST['test_mode']);
		$setting['user'] = trim($_POST['user']);
		$setting['password'] = trim($_POST['password']);
		$setting['signature'] = trim($_POST['signature']);
		$this->db->table('payment_paypal_express')->update($setting);
	}
}

?>