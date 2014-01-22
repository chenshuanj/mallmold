<?php


class notifyAction extends action
{
	//paypal notify
	public function notify_paypal()
	{
		$status = $this->model('paypal')->verify();
		if(!$status){
			echo 'Verification failed';
			return;
		}
		
		$order_id = intval($_POST['invoice']);
		$order = $this->model('order')->order_get($order_id);
		if(!$order){
			echo 'error';
			return;
		}
		
		if($order['status'] > 0){
			echo 'Completed';
			return;
		}
		
		if($_POST['payment_status'] == 'Completed' && $_POST['receiver_email'] == $this->model('paypal')->email){
			$status = $this->model('payment')->pay_status('processing');
			$this->model('payment')->status = $status;
			
			//pay log
			$remark = 'payment_status:'.$_POST['payment_status'].'; txn_id:'.$_POST['txn_id'].'; ipn_track_id:'.$_POST['ipn_track_id'];
			$data = array(
				'type' => 1,
				'order_sn' => $order['order_sn'],
				'model' => 'paypal',
				'track_id' => $_POST['ipn_track_id'],
				'currency' => $_POST['mc_currency'],
				'money' => $_POST['mc_gross'],
				'remark' => $remark,
				'time' => time()
			);
			$this->db->table('payment_log')->insert($data);
			$this->model('payment')->message = 'pay success';
			$this->model('payment')->pay_success($order);
			echo 'Completed';
			return;
		}else{
			$this->model('payment')->error_msg = 'notify error: '.var_export($_POST, true);
			$this->model('payment')->error_log($order_id, 'paypal');
			return 'error';
		}
	}
	
	public function notify_moneybookers()
	{
		$status = $this->model('moneybookers')->verify();
		if(!$status){
			echo 'Verification failed';
			return;
		}
		
		$order_id = intval($_POST['transaction_id']);
		$order = $this->model('order')->order_get($order_id);
		if(!$order){
			echo 'error';
			return;
		}
		
		if($order['status'] > 0){
			echo 'Completed';
			return;
		}
		
		if($_POST['status'] == 2){
			$status = $this->model('payment')->pay_status('processing');
			$this->model('payment')->status = $status;
			
			//pay log
			$remark = 'payment_status:'.$_POST['status'].'; pay_from_email:'.$_POST['pay_from_email'].'; track_id:'.$_POST['mb_transaction_id'];
			$data = array(
				'type' => 1,
				'order_sn' => $order['order_sn'],
				'model' => 'moneybookers',
				'track_id' => $_POST['mb_transaction_id'],
				'currency' => $_POST['mb_currency'],
				'money' => $_POST['mb_amount'],
				'remark' => $remark,
				'time' => time()
			);
			$this->db->table('payment_log')->insert($data);
			$this->model('payment')->message = 'pay success';
			$this->model('payment')->pay_success($order);
			echo 'Completed';
			return;
		}else{
			$this->model('payment')->error_msg = 'pay failed - status:'.$_POST['status'].',code:'.$_POST['failed_reason_code'];
			$this->model('payment')->error_log($order_id, 'moneybookers');
			return 'error';
		}
	}
}

?>