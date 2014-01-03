<?php


class coupon extends model
{
	public function check($code)
	{
		if(!$code){
			return false;
		}
		
		if(!preg_match("/^[0-9a-zA-Z]+$/", $code)){
			return -1;
		}
		
		$coupon = $this->db->table('coupon')->where("code='$code'")->get();
		if(!$coupon){
			return false;
		}
		
		$time = time();
		if($coupon['status'] != 0 || $time > $coupon['expiretime']){
			return 0;
		}
		
		return $coupon['id'];
	}
	
	public function get_money($id)
	{
		if(!$id){
			return 0;
		}
		$coupon = $this->db->table('coupon')->where("id=$id")->get();
		if(!$coupon){
			return 0;
		}
		$time = time();
		if($coupon['status'] != 0 || $time > $coupon['expiretime']){
			return 0;
		}
		return $coupon['money'];
	}
	
	public function set_used($id)
	{
		if(!$id){
			return false;
		}
		return $this->db->table('coupon')->where("id=$id")->update(array('status'=>1));
	}
	
	public function creat($money, $sendto, $order_id=0)
	{
		$time = time();
		$setting = &$this->model('common')->setting();
		$expire_day = intval($setting['coupon_expire_day']);
		if($expire_day > 0){
			$expiretime = $time + $expire_day*24*3600;
		}else{
			$expiretime = 0;
		}
		
		$data = array(
			'code' => $this->makecode(),
			'money' => floatval($money),
			'sendto' => $sendto,
			'create_order' => $order_id,
			'expiretime' => $expiretime,
			'createtime' => $time,
		);
		return $this->db->table('coupon')->insert($data);
	}
	
	private function makecode()
	{
		$str = 'abcdefghijkmnopqrstuvwsyzABCDEFGHIJKMNOPQRSTUVWSYZ0123456789';
		$n = strlen($str);
		$code = '';
		for($i=0; $i<12; $i++){
			$k = rand(0, $n-1);
			$code .= $str[$k];
		}
		
		$n = $this->db->table('coupon')->where("code='$code'")->count();
		if($n > 0){
			return $this->makecode();
		}else{
			return $code;
		}
	}
}
?>