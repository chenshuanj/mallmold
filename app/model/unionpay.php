<?php


class unionpay extends model
{
	private $test_mode = 0;
	private $pay_url = 'https://unionpaysecure.com/api/Pay.action';
	private $pay_url_test = 'http://58.246.226.99/UpopWeb/api/Pay.action';
	private $data = array();
	private $localpay = false;
	private $repay = true;
	
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
		$time = date('YmdHis', $order["addtime"]);
		
		$pay_params = array(
			'version'				=> '1.0.0',
			'charset'				=> 'UTF-8',
			'merId'					=> trim($this->merId),
			'merAbbr'				=> trim($this->merAbbr),
			'merCode'				=> trim($this->merCode),
			'origQid'				=> '',
			'acqCode'				=> '',
			'commodityUrl'			=> '',
			'commodityName'			=> '',
			'commodityUnitPrice'	=> '',
			'commodityQuantity'		=> '',
			'commodityDiscount'		=> '',
			'transferFee'			=> '',
			'customerName'			=> '',
			'defaultPayType'		=> '',
			'defaultBankNumber'		=> '',
			'transTimeout'			=> '',
			'merReserved'			=> '',
			'transType'				=> '01',
			'orderAmount'			=> $order["total_amount"] * 100,
			'orderNumber'			=> $time.sprintf("%06d", $order["order_id"]),
			'orderTime'				=> $time,
			'orderCurrency'			=> '156',
			'customerIp'			=> $this->load('lib/visitor')->get_ip(),
			'frontEndUrl'			=> url('order/success?order_id='.$order['order_id']),
			'backEndUrl'			=> url('notify/notify_unionpay'),
		);
		
		//sign
		$pay_params['signature'] = $this->createsign($pay_params);
		$pay_params['signMethod'] = 'md5';
		
		$this->data = $pay_params;
		return true;
	}
	
	public function get_form()
	{
		return array(
			'action' => $this->pay_url,
			'data' => $this->data,
		);
	}
	
	public function verify()
	{
		$args = $_POST;
		$signature = $args['signature'];
		unset($args['signature']);
		unset($args['signMethod']);
		
		$status = false;
		$check = $this->createsign($args);
		if($check == $signature){
			$status = true;
		}else{
			$this->model('payment')->error_msg = 'Verification failed: '.var_export($_POST, true);
			$this->model('payment')->error_log($_POST['orderNumber'], 'unionpay');
		}
		
		return $status;
	}
	
	private function createsign($pay_params)
	{
		ksort($pay_params);
		$para = '';
		foreach($pay_params as $k=>$v){
			$para .= "$k=$v&";
		}
		return md5($para.md5($this->security_key));
	}
}
?>