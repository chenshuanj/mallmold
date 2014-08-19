<?php
/*
*	@userAction.php
*	Copyright (c)2013-2014 Mallmold Ecommerce(HK) Limited. 
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

class userAction extends commonAction
{
	public function login()
	{
		if($this->model('user')->is_login()){
			header('Location: '.url('index/index'));
			return;
		}
		
		$back_url = $this->model('common')->back_url();
		if($this->is_noback_url($back_url)){
			$back_url = url('index/index');
		}
		
		if($this->setting['login_verify'] == 1){
			$this->load('lib/captcha')->set_captcha();
		}
		
		$this->view['login_verify'] = $this->setting['login_verify'];
		$this->view['back_url'] = $back_url;
		$this->view['html_title'] = lang('Login');
		$this->view['map'] = array(array('title' => lang('Login')));
		$this->view('user/login.html');
	}
	
	public function register()
	{
		if($this->model('user')->is_login()){
			header('Location: '.url('index/index'));
			return;
		}
		
		$back_url = $this->model('common')->back_url();
		if($this->is_noback_url($back_url)){
			$back_url = url('index/index');
		}
		
		if($this->setting['register_verify'] == 1){
			$this->load('lib/captcha')->set_captcha();
		}
		
		$this->view['register_verify'] = $this->setting['register_verify'];
		$this->view['back_url'] = $back_url;
		$this->view['html_title'] = lang('Register');
		$this->view['map'] = array(array('title' => lang('Register')));
		$this->view('user/register.html');
	}
	
	public function dologin()
	{
		if($this->model('user')->is_login()){
			header('Location: '.url('index/index'));
			return;
		}
		
		if($this->setting['login_verify'] == 1){
			$captcha = strtolower(trim($_POST['captcha']));
			if(!$captcha ||  $captcha != strtolower($this->load('lib/captcha')->getcode())){
				$this->load('lib/captcha')->set_captcha();
				$this->error('Captcha Error');
				return;
			}
		}
		
		$back_url = trim($_POST['back_url']);
		$email = trim($_POST['email']);
		$password = $_POST['password'];
		
		if(!$email || !$password){
			$this->error('Please fill in your email and password');
			return;
		}
		if(!$this->load('lib/filter')->is_email($email)){
			$this->error('Wrong email format');
			return;
		}
		
		$status = $this->model('user')->login($email, $password);
		if($status == 0){
			$this->error('Can not find this user');
			return;
		}elseif($status == -1){
			$this->error('Incorrect password authentication');
			return;
		}else{
			!$back_url && $back_url = url('index/index');
			header('Location: '.$back_url);
		}
	}
	
	public function doregister()
	{
		if($this->model('user')->is_login()){
			header('Location: '.url('index/index'));
			return;
		}
		
		if($this->setting['register_verify'] == 1){
			$captcha = strtolower(trim($_POST['captcha']));
			if(!$captcha ||  $captcha != strtolower($this->load('lib/captcha')->getcode())){
				$this->load('lib/captcha')->set_captcha();
				$this->error('Captcha Error');
				return;
			}
		}
		
		$back_url = trim($_POST['back_url']);
		$firstname = trim($_POST['firstname']);
		$lastname = trim($_POST['lastname']);
		$email = trim($_POST['email']);
		$password = $_POST['password'];
		$repassword = $_POST['repassword'];
		
		if(!$firstname || !$email){
			$this->error('Please fill in your email and password');
			return;
		}
		if(!$this->load('lib/filter')->is_email($email)){
			$this->error('Wrong email format');
			return;
		}
		if(!$this->load('lib/filter')->is_username($firstname)){
			$this->error('Wrong username format');
			return;
		}
		if(!$password || !$repassword){
			$this->error('Please fill in your password');
			return;
		}
		if(strlen($password) < 6){
			$this->error('Password length must be greater than 5');
			return;
		}
		if($password != $repassword){
			$this->error('Confirm password do not match');
			return;
		}
		
		$status = $this->model('user')->register($email, $password, $firstname, $lastname);
		if($status == 0){
			$this->error('Register failed');
			return;
		}elseif($status == -1){
			$this->error('This email already exists');
			return;
		}else{
			!$back_url && $back_url = url('index/index');
			header('Location: '.$back_url);
		}
	}
	
	public function logout()
	{
		$this->model('user')->logout();
		header('Location: '.$this->model('common')->back_url());
	}
	
	public function findpswd()
	{
		if($this->model('user')->is_login()){
			header('Location: '.url('index/index'));
			return;
		}
		
		$message = 0;
		if(isset($_POST['submit'])){
			$email = trim($_POST['email']);
			if(!$this->load('lib/filter')->is_email($email)){
				$this->error('Wrong email format');
				return;
			}
			
			$user = $this->db->table('user')->where("email='$email'")->get();
			if(!$user){
				$this->error('Can not find the email');
				return;
			}
			
			$tmp_password = mt_rand(100000, 999999);
			$user['password'] = $tmp_password;
			
			$mail = $this->model('notice')->getmailtpl('resetpassword');
			$this->view['user'] = $user;
			$content = $this->view('notice/'.$mail['path'], 0);
			$status = $this->model('notice')->mail($email, $mail['title'], $content);
			
			if($status){
				$this->model('user')->change_pswd($tmp_password, $user['user_id']);
				$message = 1;
			}
		}
		
		$this->view['message'] = $message;
		$this->view['html_title'] = lang('Find password');
		$this->view['map'] = array(array('title' => lang('Find password')));
		$this->view('user/findpswd.html');
	}
	
	private function is_noback_url($url)
	{
		$uri = parse_url($url);
		parse_str($uri['query'], $args);
		$c_key = $this->config['MDL_PARANAME'];
		$a_key = $this->config['ACT_PARANAME'];
		$action = array('login', 'register');
		if($args[$c_key] == 'user' && in_array($args[$a_key], $action)){
			return true;
		}else{
			return false;
		}
	}
}

?>