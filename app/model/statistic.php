<?php
/*
*	@statistic.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
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
			$this->db->table('goods')->where("goods_id='$goods_id'")->addnum('sold_num', $number);
		}
		
		return true;
	}
	
	public function add_attr_click($attr_id)
	{
		if($this->model('visitor')->is_spider()){
			return false;
		}
		
		return $this->db->table('attribute')->where("attr_id=$attr_id")->addnum('click', 1);
	}
}
?>