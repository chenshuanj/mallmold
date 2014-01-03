<?php


class paypal extends model
{
	private $refund = false;
	
	public function __construct()
    {
		parent::__construct();
	}
	
	public function can_refund()
	{
		return $this->refund;
	}
}
?>