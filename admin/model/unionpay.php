<?php


class unionpay extends model
{
	private $test_mode = 0;
	private $pay_url = 'https://unionpaysecure.com/api/Pay.action';
	private $pay_url_test = 'http://58.246.226.99/UpopWeb/api/Pay.action';
	private $refund = false;
	
	public $merId;
	public $merCode;
	public $merAbbr;
	public $security_key;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_unionpay')->get();
		$this->test_mode = $setting['test_mode'];
		$this->merId = $setting['merid'];
		$this->merCode = $setting['mercode'];
		$this->merAbbr = $setting['merabbr'];
		$this->security_key = $setting['security_key'];
		
		if($this->test_mode == 1){
			$this->pay_url = $this->pay_url_test;
		}
	}
	
	public function can_refund()
	{
		return $this->refund;
	}
}
?>