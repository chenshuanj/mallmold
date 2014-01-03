<?php


require Action('common');

class shipAction extends commonAction
{
	public function index()
	{
		$where = '';
		$ship_sn = trim($_POST['ship_sn']);
		if($ship_sn){
			$where = "ship_sn like '%$ship_sn%'";
		}
		
		$total = $this->db->table('order_ship')->where($where)->count();
		$this->pager($total);
		
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$list = $this->db->table('order_ship')->where($where)->limit($limit)->getlist();
		foreach($list as $k=>$v){
			$list[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
		}
		
		$this->view['list'] = $list;
		$this->view['ship_sn'] = $ship_sn;
		$this->view['title'] = lang('shiplist');
		$this->view('ship/list.html');
	}
	
	public function view_ship()
	{
		$this->get_ship();
		$this->view['title'] = lang('shiplist');
		$this->view('ship/view_ship.html');
	}
	
	public function print_ship()
	{
		$this->get_ship();
		$this->view['title'] = lang('print_ship');
		$this->view('ship/print_ship.html');
	}
	
	private function get_ship()
	{
		$ship_id = intval($_GET['ship_id']);
		if(!$ship_id){
			$this->error('args_error');
		}
		
		$order_ship = $this->db->table('order_ship')->where("id=$ship_id")->get();
		$order_ship['time'] = date('Y-m-d H:i:s', $order_ship['time']);
		$ship_goods = $this->db->table('order_ship_goods')->where("ship_id=$ship_id")->getlist();
		
		$order_id = $order_ship['order_id'];
		$address = $this->db->table('order_shipping_address')->where("order_id=$order_id")->get();
		$address['country'] = $this->db->table('country')->where("id=".$address['country_id'])->getval('name');
		$address['state'] = $this->db->table('region')->where("region_id=".$address['region_id'])->getval('name');
		
		$this->view['address'] = $address;
		$this->view['order_ship'] = $order_ship;
		$this->view['ship_goods'] = $ship_goods;
	}
}

?>