<?php


class paypal_pro extends model
{
	private $test_mode = 0;
	private $env_url = 'https://api-3t.paypal.com/nvp';
	private $env_url_test = 'https://api-3t.sandbox.paypal.com/nvp';
	private $refund = true;
	
	private $api_username;
	private $api_password;
	private $api_signature;
	
	public $model = 'paypal_pro';
	public $type = 'Full';
	public $amt = 0;
	public $note;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_paypal_pro')->get();
		$this->test_mode = $setting['test_mode'];
		$this->api_username = $setting['user'];
		$this->api_password = $setting['password'];
		$this->api_signature = $setting['signature'];
		
		if($this->test_mode == 1){
			$this->env_url = $this->env_url_test;
		}
	}
	
	public function can_refund()
	{
		return $this->refund;
	}
	
	public function validate($pay, $refund)
	{
		if($refund <=0 || $refund > $pay['money']){
			$this->model('payment')->error_msg = 'The refund amount can not accept, must be: 0.00 - '.$pay['money'];
			return false;
		}
		
		$this->amt = $refund;
		if($refund < $pay['money']){
			$this->type = 'Partial';
			$this->note = 'Refund';
		}
		
		return true;
	}
	
	public function refund($pay, $order)
	{
		$params = array(
			'VERSION'				=> '65.1',
			'PWD'					=> $this->api_password,
			'USER'					=> $this->api_username,
			'SIGNATURE'				=> $this->api_signature,
			'TRANSACTIONID'			=> $pay['track_id'],
			'REFUNDTYPE'			=> $this->type, //Full or Partial
			'CURRENCYCODE'			=> $pay["currency"],
			'NOTE'					=> $this->note,
		);
		
		if($this->type == 'Partial'){
			$params['AMT'] = $this->amt;
		}
		
		$req = 'METHOD=RefundTransaction';
		foreach($params as $key => $value){
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		
		$status = false;
		$content = $this->load('lib/http')->post($this->env_url, $req);
		if($content === false){
			$this->model('payment')->error_report($this->model, $this->load('lib/http')->error);
		}else{
			parse_str($content, $res);
			$ack = strtoupper($res["ACK"]);
			if($ack == 'SUCCESS'){
				$status = $this->model('payment')->pay_status('refunded');
				$this->model('payment')->status = $status;
				
				//pay log
				$remark = 'CORRELATIONID:'.$res['CORRELATIONID'].'; BUILD:'.$res['BUILD'];
				$data = array(
					'type' => 2,
					'order_sn' => $order['order_sn'],
					'model' => $this->model,
					'track_id' => $res['REFUNDTRANSACTIONID'],
					'currency' => $res['CURRENCYCODE'],
					'money' => $res['TOTALREFUNDEDAMOUNT'],
					'remark' => $remark,
					'time' => time()
				);
				$this->db->table('payment_log')->insert($data);
			
				$this->model('payment')->message = 'Refund success';
				$status = true;
			}else{
				$this->model('payment')->error_msg = $res['L_ERRORCODE0'].' '.$res['L_LONGMESSAGE0'];
				$this->model('payment')->error_log($order['order_id'], $this->model);
			}
		}
		return $status;
	}
}
?>