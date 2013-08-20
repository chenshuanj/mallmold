<?php
/*
*	@paypal.php
*	Copyright (c)2013 Mallmold Ecommerce(HK) Limited. 
*	http://www.mallmold.com/
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*	
*	If you want to get an unlimited version of the program or want to obtain
*	additional services, please send an email to <service@mallmold.com>.
*/

class paypal extends model
{
	private $env_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	private $data = array();
	private $localpay = false;
	private $repay = true;
	
	public $type = 1;
	public $email;
	public $my_public_cert_file;
	public $my_private_key_file;
	public $my_private_key_pswd;
	public $paypal_cert_id;
	public $paypal_cert_file;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_paypal')->get();
		$this->email = $setting['email'];
		$this->type = intval($setting['type']);
		$this->my_public_cert_file = $setting['my_public_cert_file'];
		$this->my_private_key_file = $setting['my_private_key_file'];
		$this->my_private_key_pswd = $setting['my_private_key_pswd'];
		$this->paypal_cert_id = $setting['paypal_cert_id'];
		$this->paypal_cert_file = $setting['paypal_cert_file'];
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
		if($this->type == 2){
			if(!$this->my_public_cert_file || !$this->my_private_key_file || !$this->paypal_cert_id
				|| !$this->my_private_key_pswd || !$this->paypal_cert_file)
			{
				$this->model('payment')->error_msg = 'Certificate is not set';
				return false;
			}
		}
		
		$country_code = $this->db->table('country')->where("id=".$order["shipping_address"]['country_id'])->getval('code');
		$state_code = $this->db->table('region')->where("region_id=".$order["shipping_address"]['region_id'])->getval('code');
		$discount_amount = $this->model('coupon')->get_money($order["coupon_id"]);
		
		$pay_params = array(
			'cmd'					=> '_cart',
			'charset'				=> 'utf-8',
			'upload'				=> '1',
			'lc'					=> $order["language"],
			'business'				=> $this->email,
			'invoice'				=> $order["order_id"],
			'currency_code'			=> $order["currency"],
			'amount'				=> $order["total_amount"],
			'tax_cart'				=> $order["tax_fee"],
			'shipping'				=> $order["shipping_fee"],
			'discount_amount_cart'	=> $discount_amount,
			
			//buyer address
			'address_override'	=> '1',
			'country'			=> $country_code,
			'state'				=> $state_code,
			'city'				=> htmlspecialchars($order["shipping_address"]['city']),
			'zip'				=> htmlspecialchars($order["shipping_address"]['postcode']),
			'address1'			=> htmlspecialchars($order["shipping_address"]['address']),
			'address2'			=> htmlspecialchars($order["shipping_address"]['address2']),
			'email'				=> $order["email"],
			'first_name'		=> htmlspecialchars($order["shipping_address"]['firstname']),
			'last_name'			=> htmlspecialchars($order["shipping_address"]['lastname']),
			
			//return
			'return'			=> htmlspecialchars(url('order/success?order_id='.$order['order_id'])),
			'cancel_return'		=> htmlspecialchars(url('account/orderview?order_id='.$order['order_id'])),
			'notify_url'		=> htmlspecialchars(url('notify/notify_paypal')),
		);
		
		$n = 1;
		foreach($order['goods'] as $goods){
			$name_key = 'item_name_'.$n;
			$quantity_key = 'quantity_'.$n;
			$price_key = 'amount_'.$n;
			
			$pay_params[$name_key] = htmlspecialchars($goods['goods_name']);
			$pay_params[$price_key] = htmlspecialchars($goods['price']);
			$pay_params[$quantity_key] = htmlspecialchars($goods['quantity']);
			
			$n++;
		}
		
		$this->data = $pay_params;
		return true;
	}
	
	public function verify()
	{
		$req = 'cmd=_notify-validate';
		foreach($_POST as $key => $value){
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		
		$status = false;
		$content = $this->load('lib/http')->post($this->env_url, $req);
		if($content === false){
			$this->model('report')->add('authorize', $this->load('lib/http')->error);
		}else{
			if(trim($content) == 'VERIFIED'){
				$status = true;
			}else{
				$this->model('payment')->error_msg = 'Verification failed: '.$content;
				$this->model('payment')->error_log($_POST['invoice'], 'paypal');
			}
		}
		return $status;
	}
	
	public function get_form()
	{
		if($this->type == 1){
			return array(
				'action' => $this->env_url,
				'data' => $this->data,
			);
		}else{
			return $this->get_encrypt_form();
		}
	}
	
	private function get_encrypt_form()
    {
		$this->data['cert_id'] = $this->paypal_cert_id;
		$this->data['charset'] = 'UTF-8';
		
		$contentBytes = array();
		foreach ($this->data as $name => $value) {
			$contentBytes[] = "$name=$value";
		}
        $contentBytes = implode("\n", $contentBytes);

		$encryptedData = $this->signAndEncrypt($contentBytes);
		if(!$encryptedData){
			return false;
		}
		
		$encryptedData = "-----BEGIN PKCS7-----".$encryptedData."-----END PKCS7-----";
		
		return array(
			'action' => $this->env_url,
			'data' => array(
						'cmd' => '_s-xclick',
						'encrypted' => $encryptedData,
					),
		);
	}
	
	private function signAndEncrypt($dataStr_)
	{
		$dataStrFile = realpath(tempnam('/tmp', 'pp_'));
        $fd = fopen($dataStrFile, 'w');
		if(!$fd){
			$this->model('payment')->error_msg = "Could not open temporary file $dataStrFile.";
			return false;
		}
		fwrite($fd, $dataStr_);
		fclose($fd);

		$signedDataFile = realpath(tempnam('/tmp', 'pp_'));
		if(!@openssl_pkcs7_sign($dataStrFile,
								$signedDataFile,
								"file://".$this->my_public_cert_file,
								array("file://".$this->my_private_key_file, $this->my_private_key_pswd),
								array(),
								PKCS7_BINARY))
		{
			unlink($dataStrFile);
			unlink($signedDataFile);
			$this->model('payment')->error_msg = "Could not sign data: ".openssl_error_string();
			return false;
		}

		unlink($dataStrFile);

		$signedData = file_get_contents($signedDataFile);
		$signedDataArray = explode("\n\n", $signedData);
		$signedData = $signedDataArray[1];
		$signedData = base64_decode($signedData);

		unlink($signedDataFile);

		$decodedSignedDataFile = realpath(tempnam('/tmp', 'pp_'));
		$fd = fopen($decodedSignedDataFile, 'w');
		if(!$fd) {
			$this->model('payment')->error_msg = "Could not open temporary file $decodedSignedDataFile.";
			return false;
		}
		fwrite($fd, $signedData);
		fclose($fd);

		$encryptedDataFile = realpath(tempnam('/tmp', 'pp_'));
		if(!@openssl_pkcs7_encrypt(	$decodedSignedDataFile,
									$encryptedDataFile,
									file_get_contents($this->paypal_cert_file),
									array(),
									PKCS7_BINARY))
		{
			unlink($decodedSignedDataFile);
			unlink($encryptedDataFile);
			$this->model('payment')->error_msg = "Could not encrypt data: ".openssl_error_string();
			return false;
		}

		unlink($decodedSignedDataFile);

		$encryptedData = file_get_contents($encryptedDataFile);
		if(!$encryptedData) {
			$this->model('payment')->error_msg = "Encryption and signature of data failed.";
			return false;
		}

		unlink($encryptedDataFile);
		$encryptedDataArray = explode("\n\n", $encryptedData);
		$encryptedData = trim(str_replace("\n", '', $encryptedDataArray[1]));
        return $encryptedData;
	}
}
?>