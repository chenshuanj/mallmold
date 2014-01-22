<?php


class payment extends model
{
	public $status = 0;
	public $error_msg = '';
	public $message;
	
	public function pay_status($code)
	{
		$status = array(
			'pending_payment' => 0,
			'payment_review' => 1,
			'cancelled' => 2,
			'processing' => 3,
			'refunded' => 4,
			'complete' => 5,
		);
		return isset($status[$code]) ? $status[$code] : 0;
	}
	
	//return: -1 Failed, 0 can't refund, 1 refund success
	public function refund($order, $refund)
	{
		$payment_id = $order['payment_id'];
		$payment = $this->db->table('payment')->where("id=$payment_id")->get();
		if(!$payment){
			$this->error_msg = 'Can not find the payment method anymore';
			return -1;
		}
		
		$model = $payment['model'];
		$status = $this->model($model)->can_refund();
		if(!$status){
			$this->error_msg = 'Can not refund';
			return 0;
		}
		
		//get Transaction ID
		$order_sn = $order['order_sn'];
		$pay = $this->db->table('payment_log')->where("order_sn='$order_sn' and type=1")->get();
		if(!$pay){
			$this->error_msg = 'Can not find the tracking information';
			return -1;
		}
		
		//validate
		$status = $this->model($model)->validate($pay, $refund);
		if(!$status){
			return -1;
		}
		
		//refund
		$status = $this->model($model)->refund($pay, $order);
		if(!$status){
			return -1;
		}else{
			return 1;
		}
	}
	
	public function error_log($order_id, $method)
	{
		$data = array(
			'order_id' => intval($order_id),
			'method' => $method,
			'error_msg' => ($this->error_msg ? addslashes($this->error_msg) : 'Unknown'),
			'time' => time()
		);
		return $this->db->table('payment_error')->insert($data);
	}
	
	public function error_report($type, $message)
	{
		$data = array(
			'type' => $type,
			'message' => $message,
			'uri' => $_SERVER['REQUEST_URI'],
			'time' => time(),
		);
		$this->db->table('error_report')->insert($data);
	}
}
?>