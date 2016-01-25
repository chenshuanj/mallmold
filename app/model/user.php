<?php
/*
*	@user.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

class user extends model
{
	public function is_login()
	{
		if(!$_SESSION['user_id']){
			return false;
		}else{
			return true;
		}
	}
	
	public function get($user_id=0)
	{
		if(!$user_id){
			$user_id = intval($_SESSION['user_id']);
		}
		return $this->db->table('user')->where("user_id='$user_id'")->get();
	}
	
	public function get_group($group_id)
	{
		if(!$group_id){
			return null;
		}
		
		$key = 'user_group_'.$group_id;
		$group = $this->cache($key);
		if(!$group){
			$group = $this->model('mdata')->table('user_group')->where("group_id='$group_id'")->get();
			$this->cache($key, $group);
		}
		return $group;
	}
	
	public function login($email, $password)
	{
		$user = $this->db->table('user')->where("email='$email'")->get();
		if(!$user){
			return 0;
		}
		
		if($this->encrypt($password, $user['salt']) != $user['password']){
			return -1;
		}
		
		$this->login_event($user['user_id']);
		return 1;
	}
	
	public function register($email, $password, $firstname, $lastname)
	{
		//if email exist
		$count = $this->db->table('user')->where("email='$email'")->count();
		if($count > 0){
			return -1;
		}
		
		$salt = $this->create_salt();
		$password = $this->encrypt($password, $salt);
		$time = time();
		
		$data = array(
			'group_id' => $this->get_default_group(),
			'firstname' => $firstname,
			'lastname' => $lastname,
			'email' => $email,
			'password' => $password,
			'salt' => $salt,
			'language' => cookie('lang'),
			'reg_time' => $time,
			'login_time' => $time,
		);
		$user_id = $this->db->table('user')->insert($data);
		if(!$user_id){
			return 0;
		}
		
		$this->login_event($user_id);
		
		$setting = &$this->model('common')->setting();
		if($setting['user_register_notice'] == 1){
			$this->model('event')->add('user.register', $user_id);
		}
		
		return 1;
	}
	
	private function login_event($user_id)
	{
		$_SESSION['user_id'] = $user_id;
		
		$time = time();
		$this->db->table('user')->where("user_id='$user_id'")->update(array('login_time' => $time));
		
		$this->model('cart')->turn_cart();
		return null;
	}
	
	public function logout()
	{
		$_SESSION['user_id'] = '';
		$this->model('checkout')->del_checkout();

		return true;
	}
	
	public function change_pswd($password, $user_id=0)
	{
		$user_id = $user_id ? $user_id : intval($_SESSION['user_id']);
		if($user_id < 1){
			return false;
		}
		
		$salt = $this->db->table('user')->where("user_id=$user_id")->getval('salt');
		$password = $this->encrypt($password, $salt);
		return $this->db->table('user')->where("user_id=$user_id")->update(array('password'=>$password));
	}
	
	public function get_default_group()
	{
		$setting = &$this->model('common')->setting();
		return $setting['user_default_group'] ? $setting['user_default_group'] : 0;
	}
	
	private function create_salt()
	{
		$str = 'abcdefghijkmnopqrstuvwsyz';
		$str .= strtoupper($str);
		$n = strlen($str);
		$k1 = rand(0, $n-1);
		$k2 = rand(0, $n-1);
		return $str[$k1].$str[$k2];
	}
	
	public function encrypt($password, $salt)
	{
		return md5(md5($password).$salt);
	}
}
?>