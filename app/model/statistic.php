<?php


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