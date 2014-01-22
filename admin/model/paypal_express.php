<?php


require_once(APP_PATH .'model/paypal_pro.php');

class paypal_express extends paypal_pro
{
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_paypal_express')->get();
		$this->test_mode = $setting['test_mode'];
		$this->api_username = $setting['user'];
		$this->api_password = $setting['password'];
		$this->api_signature = $setting['signature'];
		
		if($this->test_mode == 1){
			$this->env_url = $this->env_url_test;
		}
		
		$this->model = 'paypal_express';
	}
}
?>