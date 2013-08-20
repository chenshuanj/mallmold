<?php
/*
*	@statistic.php
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

class statistic extends model
{
	private function actions()
	{
		return array('click', 'cart', 'buy', 'delivery', 'refund');
	}
	
	public function add($goods_id, $action)
	{
		if($this->model('visitor')->is_spider()){
			return false;
		}
		
		$actions = $this->actions();
		if(!$goods_id || !in_array($action, $actions)){
			return false;
		}
		
		$row = $this->db->table('goods_statistic')->where("goods_id='$goods_id'")->get();
		if($row){
			$number = $row[$action] + 1;
			$this->db->table('goods_statistic')->where("goods_id='$goods_id'")->update(array($action=>$number));
		}else{
			$data = array(
				'goods_id' => $goods_id,
				$action => 1,
			);
			$this->db->table('goods_statistic')->insert($data);
		}
		
		if($action == 'buy'){
			$sold_num = $this->db->table('goods')->where("goods_id='$goods_id'")->getval('sold_num');
			$this->db->table('goods')->where("goods_id='$goods_id'")->update(array('sold_num'=> $sold_num + $number));
		}
		
		return true;
	}
	
	public function add_attr_click($attr_id)
	{
		if($this->model('visitor')->is_spider()){
			return false;
		}
		
		$click = $this->db->table('attribute')->where("attr_id=$attr_id")->getval('click');
		return $this->db->table('attribute')->where("attr_id=$attr_id")->update(array('click'=> ++$click));
	}
}
?>