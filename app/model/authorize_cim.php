<?php


class authorize_cim extends model
{
	//private $api_host = 'api.authorize.net';
	private $api_host = 'apitest.authorize.net';
	private $api_path = '/xml/v1/request.api';
	private $api_id = '6nrM7QzAM6z';
	private $api_key = '2Z3kT2wmLW62dB6t';
	private $localpay = true;
	
	public $order_id;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = $this->db->table('payment_authorize')->get();
		$this->api_id = $setting['api_id'];
		$this->api_key = $setting['api_key'];
	}
	
	public function can_localpay()
	{
		return $this->localpay;
	}
	
	public function validate()
	{
		
		
		return true;
	}
	
	public function createCustomer($uid, $email, $description='')
	{
		$content = '<?xml version="1.0" encoding="utf-8"?>
					<createCustomerProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
					'.$this->authblock().'
					<profile>
						<merchantCustomerId>'.$uid.'</merchantCustomerId>
						<description>'.$description.'</description>
						<email>'.$email.'</email>
					</profile>
					</createCustomerProfileRequest>';
		return $this->send_request($content);
	}
	
	public function getCustomer($customerid)
	{
		$content = '<?xml version="1.0" encoding="utf-8"?>
					<getCustomerProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
					'.$this->authblock().'
					<customerProfileId>'.$customerid.'</customerProfileId>
					</getCustomerProfileRequest>';
		return $this->send_request($content);
	}
	
	public function delCustomer($customerid)
	{
		$content = '<?xml version="1.0" encoding="utf-8"?>
					<deleteCustomerProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
					'.$this->authblock().'
					<customerProfileId>'.$customerid.'</customerProfileId>
					</deleteCustomerProfileRequest>';
		return $this->send_request($content);
	}
	
	public function createShippingAddress($profileId, $address)
	{
		$content = '<?xml version="1.0" encoding="utf-8"?>
					<createCustomerShippingAddressRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
					'.$this->authblock().'
					<customerProfileId>'.$profileId.'</customerProfileId>
					<address>
						<firstName>'.$address['firstName'].'</firstName>
						<lastName>'.$address['lastName'].'</lastName>
						<phoneNumber>'.$address['phoneNumber'].'</phoneNumber>
					</address>
					</createCustomerShippingAddressRequest>';
		return $this->send_request($content);
	}
	
	public function delShippingAddress($customerid, $addressId)
	{
		$content = '<?xml version="1.0" encoding="utf-8"?>
					<deleteCustomerShippingAddressRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
					'.$this->authblock().'
					<customerProfileId>'.$customerid.'</customerProfileId>
					<customerAddressId>'.$addressId.'</customerAddressId>
					</deleteCustomerShippingAddressRequest>';
		return $this->send_request($content);
	}
	
	public function createPaymentProfile($profileId, $payment)
	{
		$content = '<?xml version="1.0" encoding="utf-8"?>
					<createCustomerPaymentProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
					'.$this->authblock().'
					<customerProfileId>'.$profileId.'</customerProfileId>
					<paymentProfile>
					<payment>
						<creditCard>
							<cardNumber>'.$payment['cardNumber'].'</cardNumber>
							<expirationDate>'.$payment['expirationDate'].'</expirationDate>
							<cardCode>'.$payment['cardCode'].'</cardCode>
						</creditCard>
					</payment>
					</paymentProfile>
					</createCustomerPaymentProfileRequest>';
		return $this->send_request($content);
	}
	
	public function delPaymentProfile($profileId, $paymentProfileId)
	{
		$content = '<?xml version="1.0" encoding="utf-8"?>
					<deleteCustomerPaymentProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
					'.$this->authblock().'
					<customerProfileId>'.$profileId.'</customerProfileId>
					<customerPaymentProfileId>'.$paymentProfileId.'</customerPaymentProfileId>
					</deleteCustomerPaymentProfileRequest>';
		return $this->send_request($content);
	}
	
	public function createTransaction($profileId, $paymentProfileId, $addressId, $order)
	{
		$content = '<?xml version="1.0" encoding="utf-8"?>
					<createCustomerProfileTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
					'.$this->authblock().'
					<transaction>
					<profileTransAuthOnly>
						<amount>'. $order['amount'] .'</amount>
						<tax>
							<amount>'. floatval($order['tax']) .'</amount>
						</tax>
						<shipping>
							<amount>'. $order['fee'] .'</amount>
							<name><![CDATA['. $order['fee_name'] .']]></name>
						</shipping>';
		
		foreach($order['goods'] as $item){
			$content .= '<lineItems>
							<itemId>'.$item['item_id'].'</itemId>
							<name><![CDATA['.mb_substr($item['item_name'], 0, 30).']]></name>
							<quantity>'.$item['qty'].'</quantity>
							<unitPrice>'.$item['item_price'].'</unitPrice>
						</lineItems>';
		}
		
		$content .=		'<customerProfileId>'.$profileId.'</customerProfileId>
						<customerPaymentProfileId>'.$paymentProfileId.'</customerPaymentProfileId>
						<customerShippingAddressId>'.$addressId.'</customerShippingAddressId>
						<order>
							<invoiceNumber>'.$order['invoiceNumber'].'</invoiceNumber>
							<purchaseOrderNumber>'. $order['order_id'] .'</purchaseOrderNumber>
						</order>
					</profileTransAuthOnly>
					</transaction>
					</createCustomerProfileTransactionRequest>';
		return $this->send_request($content);
	}
	
	public function transRefund($profileId, $paymentProfileId, $amount)
	{
		$content = '<?xml version="1.0" encoding="utf-8"?>
					<createCustomerProfileTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
					'.$this->authblock().'
					<transaction>
					<profileTransRefund> 
						<amount>'.$amount.'</amount> 
						<customerProfileId>'.$profileId.'</customerProfileId> 
						<customerPaymentProfileId>'.$paymentProfileId.'</customerPaymentProfileId> 
					</profileTransRefund> 
					</transaction>
					</createCustomerProfileTransactionRequest>';
		return $this->send_request($content);
	}
	
	private function authblock()
	{
		$xml = '<merchantAuthentication>
					<name>'.$this->api_id.'</name>
					<transactionKey>'.$this->api_key.'</transactionKey>
				</merchantAuthentication>';
		return $xml;
	}
	
	private function send_request($content)
	{
		$posturl = "ssl://" . $this->api_host;
		$header = "Host: {$this->api_host}\r\n";
		$header .= "User-Agent: PHP Script\r\n";
		$header .= "Content-Type: text/xml\r\n";
		$header .= "Content-Length: ".strlen($content)."\r\n";
		$header .= "Connection: close\r\n\r\n";
		$fp = fsockopen($posturl, 443, $errno, $errstr, 30);
		if (!$fp){
			$body = false;
		}else{
			error_reporting(E_ERROR);
			fputs($fp, "POST {$this->api_path}  HTTP/1.1\r\n");
			fputs($fp, $header.$content);
			$response = "";
			while (!feof($fp)){
				$response = $response . fgets($fp, 128);
			}
			fclose($fp);
			$len = strlen($response);
			$bodypos = strpos($response, "\r\n\r\n");
			if ($bodypos <= 0){
				$bodypos = strpos($response, "\n\n");
			}
			while ($bodypos < $len && $response[$bodypos] != '<'){
				$bodypos++;
			}
			$body = substr($response, $bodypos);
		}
		return $this->parse_response($body);
	}
	
	private function parse_response($content)
	{
		$rs = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOWARNING);
		if ($rs->messages->message->code != 'I00001'){
			$this->model('payment')->error_msg = $rs->messages->resultCode.': '.$rs->messages->message->text;
			$this->model('payment')->error_log($this->order_id, 'authorize');
			return null;
		}else{
			return $rs;
		}
	}
}
?>