<?php
/*
*	@orderAction.php
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

require Action('common');

class orderAction extends commonAction
{
	public function index()
	{
		$where = '';
		$order_sn = trim($_POST['order_sn']);
		if($order_sn){
			$where = "order_sn like '%$order_sn%'";
		}
		
		$total = $this->db->table('order')->where($where)->count();
		$this->pager($total);
		
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$order_list = $this->db->table('order')->where($where)->limit($limit)->order('addtime desc')->getlist();
		foreach($order_list as $k=>$v){
			$order_list[$k]['time'] = date('Y-m-d H:i:s', $v['addtime']);
			$address = $this->db->table('order_billing_address')->where("order_id=".$v['order_id'])->get();
			$address['country'] = $this->db->table('country')->where("id=".$address['country_id'])->getval('name');
			$address['state'] = $this->db->table('region')->where("region_id=".$address['region_id'])->getval('name');
			$order_list[$k]['address'] = $address;
		}
		
		$symbols = array();
		$currencies = &$this->model('common')->currencies();
		foreach($currencies as $v){
			$symbols[$v['code']] = $v['symbol'];
		}
		
		$this->view['order_status'] = $this->model('order')->order_status();
		$this->view['symbols'] = $symbols;
		$this->view['list'] = $order_list;
		$this->view['order_sn'] = $order_sn;
		$this->view['title'] = lang('order');
		$this->view('order/list.html');
	}
	
	public function view_order()
	{
		$order_id = intval($_GET['order_id']);
		if(!$order_id){
			$this->error('args_error');
		}
		
		$order = $this->model('order')->order_get($order_id);
		if($order['user_id']>0){
			$buyer = $this->db->table('user')->where("user_id=".$order['user_id'])->get();
			if($buyer['group_id'] > 0){
				$group = $this->mdata('user_group')->where('group_id='.intval($data['group_id']))->get();
				$buyer['group'] = $group['name'];
			}else{
				$buyer['group'] = lang('none_group');
			}
			$buyer['time'] = date('Y-m-d H:i:s', $buyer['reg_time']);
			$this->view['buyer'] = $buyer;
		}
		
		$this->view['order'] = $order;
		$this->view['order_status'] = $this->model('order')->order_status();
		$this->view['edit_status'] = $this->model('order')->edit_status($order['status']);
		$this->view['title'] = lang('view_order');
		$this->view('order/view_order.html');
	}
	
	public function print_order()
	{
		$order_id = intval($_GET['order_id']);
		if(!$order_id){
			$this->error('args_error');
		}
		$this->view['order'] = $this->model('order')->order_get($order_id);
		$this->view['order_status'] = $this->model('order')->order_status();
		$this->view['title'] = lang('print_order');
		$this->view('order/print_order.html');
	}
	
	public function edit()
	{
		$order_id = intval($_POST['order_id']);
		if(!$order_id){
			$this->error('args_error');
		}
		
		$status = intval($_POST['status']);
		$edit_status = $this->model('order')->edit_status($order['status']);
		if(!$edit_status[$status]){
			$this->error('error_status');
		}
		
		$notice = intval($_POST['notice']);
		$remark = trim($_POST['remark']);
		
		$data = array(
			'order_id' => $order_id,
			'status' => $status,
			'remark' => $remark,
			'notice' => $notice,
			'time' => time(),
		);
		$this->db->table('order_status')->insert($data);
		$this->db->table('order')->where("order_id=$order_id")->update(array('status' => $status));
		
		//email notice
		if($notice == 1){
			$this->notice($order_id, 'order_update');
		}
		
		$this->ok('edit_success', url('order/view_order?order_id='.$order_id));
	}
	
	public function ship()
	{
		if(!$_POST['submit']){
			$order_id = intval($_GET['order_id']);
			if(!$order_id){
				$this->error('args_error');
			}
			
			$order = $this->model('order')->order_get($order_id);
			$this->view['order'] = $order;
			$this->view['title'] = lang('ship_order');
			$this->view('order/ship.html');
		}else{
			$order_id = intval($_POST['order_id']);
			$quantity = $_POST['quantity'];
			$ship_sn = trim($_POST['ship_sn']);
			if(!$order_id){
				$this->error('args_error');
			}
			if(!$ship_sn){
				$this->error('sn_null');
			}
			if(!array_sum($quantity)){
				$this->error('quantity_null');
			}
			
			$order = $this->model('order')->order_get($order_id);
			
			//ship
			$ship_goods = array();
			$allship = 1;
			foreach($order['goods'] as $v){
				$key = $v['id'];
				if($quantity[$key] > 0){
					if($quantity[$key] < $v['quantity']){
						$allship = 0;
					}
					
					$ship_options = array();
					if($v['options']){
						$options = json_decode($v['options'], true);
						foreach($options as $op){
							$name = $op['name'];
							$ship_options[$name] = $op['value'];
						}
					}
					$data = array(
						'goods_id' => $v['goods_id'],
						'sku' => $v['goods_sku'],
						'title' => $v['goods_name'],
						'options' => addslashes(json_encode($ship_options)),
						'quantity' => $quantity[$key],
					);
					$ship_goods[] = $data;
				}else{
					$allship = 0;
				}
			}
			
			if(!$ship_goods){
				$this->error('cannot_ship');
			}
			
			$data = array(
				'type' => 1,
				'order_id' => $order['order_id'],
				'order_sn' => $order['order_sn'],
				'ship_sn' => $ship_sn,
				'time' => time(),
			);
			$ship_id = $this->db->table('order_ship')->insert($data);
			foreach($ship_goods as $v){
				$v['ship_id'] = $ship_id;
				$this->db->table('order_ship_goods')->insert($v);
				
				//statistic
				$this->model('statistic')->add($v['goods_id'], 'delivery');
			}
			
			//status
			if($allship == 1){
				$status = 5;
			}else{
				$status = 3;
			}
			
			$this->db->table('order')->where("order_id=$order_id")->update(array('status' => $status));
			foreach($order['goods'] as $v){
				$key = $v['id'];
				if($quantity[$key] > 0){
					$this->db->table('order_goods')
						->where("id=$key")
						->update(array('shipping' => $quantity[$key]+$v['quantity']));
				}
			}
			
			//add status
			$data = array(
				'order_id' => $order_id,
				'status' => $status,
				'remark' => lang('ship'),
				'notice' => 1,
				'time' => time(),
			);
			$this->db->table('order_status')->insert($data);
			
			$this->notice($order_id, 'order_ship');
			
			$this->ok('edit_success', url('order/view_order?order_id='.$order_id));
		}
	}
	
	public function refund()
	{
		if(!$_POST['submit']){
			$order_id = intval($_GET['order_id']);
			if(!$order_id){
				$this->error('args_error');
			}
			
			$order = $this->model('order')->order_get($order_id);
			$this->view['order'] = $order;
			$this->view['title'] = lang('order_refund');
			$this->view('order/refund.html');
		}else{
			$order_id = intval($_POST['order_id']);
			$refund = floatval($_POST['refund']);
			if(!$order_id){
				$this->error('args_error');
			}
			if(!$refund){
				$this->error('refundfee_error');
			}
			
			$order = $this->model('order')->order_get($order_id);
			if($refund > $order['total_amount']){
				$this->error('refundfee_morethan_orderamount');
			}
			
			$rs = $this->model('payment')->refund($order, $refund);
			if($rs != 1){
				$this->error($this->model('payment')->error_msg);
			}
			
			//status
			$this->db->table('order')->where("order_id=$order_id")->update(array('status' => 4));
			$data = array(
				'order_id' => $order_id,
				'status' => 4,
				'remark' => lang('refund'),
				'notice' => 1,
				'time' => time(),
			);
			$this->db->table('order_status')->insert($data);
			
			$this->notice($order_id, 'order_refund');
			
			$this->ok('edit_success', url('order/view_order?order_id='.$order_id));
		}
	}
	
	private function notice($order_id, $type)
	{
		$order = $this->model('order')->order_get($order_id);
		$this->view['order'] = $order;
		
		$mail = $this->model('notice')->getmailtpl($type);
		if(!$mail){
			$this->error('cannot_find_emailtpl');
			return false;
		}
		$content = $this->view('notice/'.$mail['path'], 0);
		
		return $this->model('notice')->mail($order['email'], $mail['title'], $content);
	}
}

?>