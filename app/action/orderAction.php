<?php


require Action('common');

class orderAction extends commonAction
{
	public function pay()
	{
		$order_id = intval($_GET['order_id']);
		if(!$order_id){
			$this->error('Parameter error');
			return;
		}
		
		$order = $this->model('order')->order_get($order_id);
		
		//status
		if($order['status'] != 0){
			$this->error('Wrong order status');
			return;
		}
		
		//payment
		$payment = $this->model('payment')->get_payment($order['payment_id'], $order['shipping_address']['country_id']);
		if(!$payment){
			$this->error('Payment is invalid');
			return;
		}
		
		$_SESSION['last_order'] = $order_id;
		$model = $payment['model'];
		return $this->$model($order);
	}
	
	private function alipay($order)
	{
		$status = $this->model('alipay')->set_pay_params($order);
		if(!$status){
			$this->model('payment')->error_log($order['order_id'], 'alipay');
			$this->error($this->model('payment')->error_msg);
			return;
		}
		
		$this->view['data'] = $this->model('alipay')->get_form();
		$this->view['html_title'] = 'Redirect to Alipay';
		$this->view('checkout/alipay.html');
	}
	
	private function tenpay($order)
	{
		$status = $this->model('tenpay')->set_pay_params($order);
		if(!$status){
			$this->model('payment')->error_log($order['order_id'], 'tenpay');
			$this->error($this->model('payment')->error_msg);
			return;
		}
		
		$this->view['data'] = $this->model('tenpay')->get_form();
		$this->view['html_title'] = 'Redirect to tenpay';
		$this->view('checkout/tenpay.html');
	}
	
	private function unionpay($order)
	{
		$status = $this->model('unionpay')->set_pay_params($order);
		if(!$status){
			$this->model('payment')->error_log($order['order_id'], 'unionpay');
			$this->error($this->model('payment')->error_msg);
			return;
		}
		
		$this->view['data'] = $this->model('unionpay')->get_form();
		$this->view['html_title'] = 'Redirect to unionpay';
		$this->view('checkout/unionpay.html');
	}
	
	private function paypal($order)
	{
		$status = $this->model('paypal')->set_pay_params($order);
		if(!$status){
			$this->model('payment')->error_log($order['order_id'], 'paypal');
			$this->error($this->model('payment')->error_msg);
			return;
		}
		
		$this->view['data'] = $this->model('paypal')->get_form();
		$this->view['html_title'] = 'Redirect to Paypal';
		$this->view('checkout/paypal.html');
	}
	
	private function paypal_express($order)
	{
		$status = $this->model('paypal_express')->set_pay_params($order);
		if(!$status){
			$this->model('payment')->error_log($order['order_id'], 'paypal_express');
			$this->error($this->model('payment')->error_msg);
			return;
		}
		
		$this->view['data'] = $this->model('paypal_express')->get_form();
		$this->view['html_title'] = 'Redirect to Paypal';
		$this->view('checkout/paypal_express.html');
	}
	
	public function paypal_express_review()
	{
		$step = trim($_POST['step']);
		if($step != 'pay'){
			$status = $this->model('paypal_express')->verify();
			if(!$status){
				$this->error($this->model('payment')->error_msg);
				return;
			}
		
			$order_id = intval($_GET['order_id']);
			if(!$order_id){
				$this->error('Parameter error: Missing order_id');
				return;
			}
			
			$res = $this->model('paypal_express')->getdata();
			$data = array(
				'token' => $res['TOKEN'],
				'payer_id' => $res['PAYERID'],
				'paymentAmount' => $res['AMT'],
				'currCodeType' => $res['CURRENCYCODE'],
				'TotalAmount' => $res['AMT'],
			);
			
			$this->view['order_id'] = $order_id;
			$this->view['data'] = $data;
			$this->view['order_total'] = $res['CURRENCYCODE'].' '.$_REQUEST['currencyCodeType'].$data['TotalAmount'];
			$this->view['html_title'] = 'Order Review';
			$this->view('checkout/paypal_express_review.html');
		}else{
			$status = $this->model('paypal_express')->pay();
			if(!$status){
				$this->error($this->model('payment')->error_msg);
				return;
			}else{
				$order_id = intval($_POST['order_id']);
				$order = $this->model('order')->order_get($order_id);
				if(!$order){
					$this->error('Error: Can not find order');
					return;
				}
				
				$status = $this->model('payment')->pay_status('processing');
				$this->model('payment')->status = $status;
				$res = $this->model('paypal_express')->getdata();
				
				//pay log
				$remark = 'ACK:'.$res['ACK'].'; CORRELATIONID:'.$res['CORRELATIONID'].'; BUILD:'.$res['BUILD'];
				$data = array(
					'type' => 1,
					'order_sn' => $order['order_sn'],
					'model' => 'paypal_express',
					'track_id' => $res['TRANSACTIONID'],
					'currency' => $res['CURRENCYCODE'],
					'money' => $res['AMT'],
					'remark' => $remark,
					'time' => time()
				);
				$this->db->table('payment_log')->insert($data);
				$this->model('payment')->message = 'pay success';
				$this->model('payment')->pay_success($order);
				
				$url = url('order/success?order_id='.$order_id);
				header("Location: $url");
			}
		}
	}
	
	private function moneybookers($order)
	{
		$status = $this->model('moneybookers')->set_pay_params($order);
		if(!$status){
			$this->model('payment')->error_log($order['order_id'], 'moneybookers');
			$this->error($this->model('payment')->error_msg);
			return;
		}
		
		$this->view['data'] = $this->model('moneybookers')->get_form();
		$this->view['html_title'] = 'Redirect to MoneyBookers';
		$this->view('checkout/moneybookers.html');
	}
	
	private function authorize($order)
	{
		$this->error('Payment is invalid');
		return;
	}
	
	public function success()
	{
		$order_id = intval($_GET['order_id']);
		if(!$order_id){
			$this->error('Parameter error');
			return;
		}
		
		if($order_id != $_SESSION['last_order']){
			header('Location: '.url('account/orderview?order_id='.$order_id));
			return;
		}
		
		$_SESSION['last_order'] = null;
		
		$order = $this->model('order')->order_get($order_id);
		if(!$order){
			$this->error('Parameter error');
			return;
		}
		
		//status
		if($order['status'] == 0){
			header('Location: '.url('account/orderview?order_id='.$order_id));
			return;
		}
		
		$this->view['order'] = $order;
		$this->view['html_title'] = 'Order success';
		$this->view('checkout/success.html');
	}
}
?>