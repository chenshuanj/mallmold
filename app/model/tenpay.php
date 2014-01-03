<?php


class tenpay extends model
{
	private $test_mode = 0;
	private $api_address = 'https://api.tenpay.com';
	private $api_address_test = 'https://sandbox.tenpay.com/api';
	private $data = array();
	private $localpay = false;
	private $repay = true;
	private $notify_id;
	
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
	
	public function can_localpay()
	{
		return $this->localpay;
	}
	
	public function can_repay()
	{
		return $this->repay;
	}
	
	public function validate()
	{
		return true;
	}
	
	public function set_pay_params($order)
	{
		$pay_params = array(
			'appid'					=> trim($this->appid),
			'out_trade_no'			=> $order["order_id"],
			'total_fee'				=> $order["total_amount"]*100,
			'notify_url'			=> url('notify/notify_tenpay'),
			'return_url'			=> url('order/success?order_id='.$order['order_id']),
			'body'					=> '订单：'.$order["order_id"], //订单描述
			'spbill_create_ip'		=> $this->model('visitor')->get_ip(),
		);
		
		//sign
		$sign = $this->createsign($pay_params);
		$query = '';
		foreach($pay_params as $k=>$v){
			$query .= ($query ? '' : '&').$k.'='.urlencode($v);
		}
		
		$this->data = $query.'&sign='.$sign;
		return true;
	}
	
	public function get_form()
	{
		return $this->api_address.'/gateway/pay.htm?'.$this->data;
	}
	
	public function verify()
	{
		$para = array_merge($_GET, $_POST);
		$status = false;
		if($para['retcode'] == 0 && $para['sign']){
			$sign = strtolower($para['sign']);
			$signStr = $this->createsign($para);
			if($sign == $signStr){
				$status = true;
				$this->notify_id = $para['notify_id'];
			}else{
				$this->model('payment')->error_msg = 'Verification return false: '.$verify;
			}
		}else{
			$this->model('payment')->error_msg = 'Verification failed: '.var_export($_POST, true);
		}
		
		if($status == false){
			$this->model('payment')->error_log($para['out_trade_no'], 'tenpay');
		}
		
		return $status;
	}
	
	private function createsign($pay_params)
	{
		ksort($pay_params);
		$para = '';
		foreach($pay_params as $k=>$v){
			if($v && $v != 'null' && $v != 'sign'){
				$para .= ($para ? '&' : '').$k.'='.$v;
			}
		}
		if(get_magic_quotes_gpc()){
			$para = stripslashes($para);
		}
		return md5($para.'&key='.$this->key);
	}
}
?>