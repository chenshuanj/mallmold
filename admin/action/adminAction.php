<?php


class adminAction extends action
{
	public function __construct()
    {
		parent::__construct();
		require(APP_PATH .'model/functions.php');
	}
	
	public function login()
	{
		if($this->model('login')->checklogin()){
			header('Location: '.url('index/index'));
			return;
		}
		
		$setting = &$this->model('common')->setting();
		if($setting['admin_login_verify'] == 1){
			$this->load('lib/captcha')->set_captcha();
		}
		
		$this->view['admin_login_verify'] = $setting['admin_login_verify'];
		$this->view['title'] = lang('login');
		$this->view('login.html');
	}
	
	public function dologin()
	{
		$setting = &$this->model('common')->setting();
		if($setting['admin_login_verify'] == 1){
			$captcha = strtolower(trim($_POST['captcha']));
			if(!$captcha ||  $captcha != strtolower($this->load('lib/captcha')->getcode())){
				$this->load('lib/captcha')->set_captcha();
				echo lang('captcha_error');
				return;
			}
		}
		
		$username = trim($_POST['username']);
		$password = $_POST['password'];
		if(!preg_match("/^[0-9a-zA-Z]+$/", $username)){
			if($setting['admin_login_verify'] == 1){
				$this->load('lib/captcha')->set_captcha();
			}
			echo lang('username_error');
			return;
		}
		
		if($this->model('login')->dologin($username, $password)){
			header('Location: '.url('index/index'));
		}else{
			header('Location: '.url('admin/login'));
		}
	}
	
	public function logout()
	{
		$this->model('login')->logout();
		header('Location: '.url('admin/login'));
	}
}

?>