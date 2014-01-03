<?php


class order extends model
{
	public function order_status()
	{
		return array(
			0 => lang('Pending_Payment'),
			1 => lang('payment_review'),
			2 => lang('canceled'),
			3 => lang('processing'),
			4 => lang('refunded'), //Full refund
			5 => lang('complete'), //Contains a partial refund
		);
	}
	
	public function shipping_status(){
		return array(
			0 => lang('undelivered'),
			1 => lang('partial_delivered'),
			2 => lang('delivered'),
		);
	}
	
	public function edit_status($status=0)
	{
		$status_key = array();
		$order_status = $this->order_status();
		switch($status){
			case 0:
				$status_key= array(0,2,3);
				break;
			case 1:
				$status_key= array(0,1,2,3);
				break;
			case 2:
				$status_key= array(2);
				break;
			case 3:
				$status_key= array(2,3,4,5);
				break;
			case 4:
				$status_key= array(4);
				break;
			case 5:
				$status_key= array(4,5);
				break;
		}
		$edit_status = array();
		foreach($status_key as $v){
			$edit_status[$v] = $order_status[$v];
		}
		return $edit_status;
	}
	
	public function order_get($order_id)
	{
		$order = $this->db->table('order')->where("order_id=$order_id")->get();
		$code = $order['currency'];
		$order['time'] = date('Y-m-d H:i:s', $order['addtime']);
		$order['symbol'] = $this->db->table('currency')->where("code='$code'")->getval('symbol');
		$order['shipping_method'] = $this->db->table('shipping')->where("shipping_id=".$order['shipping_id'])->getval('name');
		
		if($order['payment_id'] > 0){
			$order['payment_method'] = $this->db->table('payment')->where("id=".$order['payment_id'])->getval('name');
		}else{
			$order['payment_method'] = lang('Do not need pay');
		}
		
		$address = $this->db->table('order_shipping_address')->where("order_id=$order_id")->get();
		$address['country'] = $this->db->table('country')->where("id=".$address['country_id'])->getval('name');
		$address['state'] = $this->db->table('region')->where("region_id=".$address['region_id'])->getval('name');
		
		$billing_address = $this->db->table('order_billing_address')->where("order_id=$order_id")->get();
		$billing_address['country'] = $this->db->table('country')->where("id=".$billing_address['country_id'])->getval('name');
		$billing_address['state'] = $this->db->table('region')->where("region_id=".$billing_address['region_id'])->getval('name');
		
		$order['address'] = $address;
		$order['billing_address'] = $billing_address;
		
		$goods = $this->db->table('order_goods')->where("order_id=$order_id")->getlist();
		foreach($goods as $k=>$v){
			$goods[$k]['goods_sku'] = $this->db->table('goods')->where("goods_id=".$v['goods_id'])->getval('sku');
		}
		$order['goods'] = $goods;
		
		$status_list = $this->db->table('order_status')->where("order_id=$order_id")->order('time desc')->getlist();
		foreach($status_list as $k=>$v){
			$status_list[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
		}
		$order['status_list'] = $status_list;
		return $order;
	}
}
?>