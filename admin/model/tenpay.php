<?php


class tenpay extends model
{
	private $test_mode = 0;
	private $api_address = 'https://api.tenpay.com';
	private $api_address_test = 'https://sandbox.tenpay.com/api';
	private $refund = false;
	
	public $appid;
	public $key;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_tenpay')->get();
		$this->test_mode = $setting['test_mode'];
		$this->appid = $setting['appid'];
		$this->key = $setting['key'];
		
		if($this->test_mode == 1){
			$this->api_address = $this->api_address_test;
		}
	}
	
	public function can_refund()
	{
		return $this->refund;
	}
}
?>