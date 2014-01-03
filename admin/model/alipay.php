<?php


class alipay extends model
{
	private $test_mode = 0;
	private $gateway = 'https://mapi.alipay.com/gateway.do';
	private $gateway_test = 'http://openapi.alipaydev.com/gateway.do';
	private $verify_url = 'http://notify.alipay.com/trade/notify_query.do';
	private $refund = false;
	
	public $seller_email;
	public $partner;
	public $key;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_alipay')->get();
		$this->test_mode = $setting['test_mode'];
		$this->seller_email = $setting['seller_email'];
		$this->partner = $setting['partner'];
		$this->key = $setting['key'];
		
		if($this->test_mode == 1){
			$this->gateway = $this->gateway_test;
		}
	}
	
	public function can_refund()
	{
		return $this->refund;
	}
}
?>