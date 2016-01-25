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
		return true;
	}
	
	public function get_top5($type)
	{
		$actions = $this->actions();
		if(!in_array($type, $actions)){
			return false;
		}
		
		$list = $this->db->table('goods', 'g')->field('g.goods_id,g.title_key_,g.sku')
					->leftjoin('goods_statistic', 's', 's.goods_id=g.goods_id')->addfield("s.$type")
					->order("$type desc")
					->limit(5)
					->getlist();
		foreach($list as $k=>$row){
			$list[$k] = $this->model('dict')->getdict($row);
		}
		return $list;
	}
	
	public function get_attr_top5()
	{
		return $this->model('mdata')->table('attribute')
									->where('status=1 and can_filter=1')
									->order('click desc')
									->limit(5)
									->getlist();
	}
}
?>