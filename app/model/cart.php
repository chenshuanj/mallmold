<?php
/*
*	@cart.php
*	Copyright (c)2013-2014 Mallmold Ecommerce(HK) Limited. 
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

class cart extends model
{
	public function cart_condition()
	{
		$user_id = $_SESSION['user_id'];
		if($user_id > 0){
			$where = "user_id=$user_id";
		}else{
			$where = "session_id='".session_id()."'";
		}
		return $where;
	}
	
	public function get_num()
	{
		$where = $this->cart_condition();
		return $this->db->table('cart')->where($where)->count();
	}
	
	public function get_qty()
	{
		$where = $this->cart_condition();
		$field = "sum(quantity) as qty";
		$row = $this->db->table('cart')->field($field)->where($where)->get();
		return $row['qty'];
	}
	
	public function get_total()
	{
		$where = $this->cart_condition();
		$field = "sum(subtotal) as total";
		$row = $this->db->table('cart')->field($field)->where($where)->get();
		return $row['total'];
	}
	
	public function get_total_weight()
	{
		$weight = 0;
		$where = $this->cart_condition();
		$list = $this->db->table('cart')->field('goods_id,quantity')->where($where)->getlist();
		foreach($list as $v){
			$goods_weight = $this->db->table('goods')->where("goods_id=".$v['goods_id'])->getval('weight');
			$weight += $goods_weight*$v['quantity'];
		}
		return $weight;
	}
	
	public function getlist()
	{
		$where = $this->cart_condition();
		$list = $this->db->table('cart')->where($where)->getlist();
		foreach($list as $k=>$v){
			$goods_id = $v['goods_id'];
			$goods = $this->model('mdata')->table('goods')->where("goods_id=$goods_id")->get();
			$goods['url'] = $this->model('urlkey')->geturl('goods_id', $goods['goods_id'], $goods['urlkey']);
			$list[$k]['goods'] = $goods;
			$list[$k]['price'] = $goods['price'];
			$options = $v['options'];
			if($options){
				$options_list = $this->model('goods')->get_price_option($goods_id);
				$options = json_decode($options, true);
				$options_name = array();
				foreach($options as $op_id=>$id){
					$options_name[$op_id] = array(
						'name' => $options_list[$op_id]['name'],
						'value' => $options_list[$op_id]['option'][$id]['name'],
						'price' => $options_list[$op_id]['option'][$id]['price'],
					);
					$list[$k]['price'] += $options_name[$op_id]['price'];
				}
				$list[$k]['options_name'] = $options_name;
			}
			
			$list[$k]['price'] = $this->model('common')->current_price($list[$k]['price'], 0);
		}
		
		return $list;
	}
	
	public function add($goods_id, $quantity, $option=array())
	{
		is_array($options) && ksort($options);
		$options = $option ? json_encode($option) : '';
		
		//if exist
		$where = $this->cart_condition();
		$where .= " and goods_id=$goods_id and options='$options'";
		$cart = $this->db->table('cart')->where($where)->get();
		if($cart){
			$this->update($cart['id'], $quantity);
			return 1;
		}else{
			$subtotal = $this->count_subtotal($goods_id, $quantity, $option);
			$data = array(
				'user_id' => ($_SESSION['user_id'] ? $_SESSION['user_id'] : 0),
				'session_id' => session_id(),
				'goods_id' => $goods_id,
				'options' => $options,
				'quantity' => $quantity,
				'currency' => $this->model('common')->current_cur(),
				'subtotal' => $subtotal,
				'addtime' => time(),
			);
			$this->model('statistic')->add($goods_id, 'cart');
			return $this->db->table('cart')->insert($data);
		}
	}
	
	public function update($id, $quantity)
	{
		$cart = $this->db->table('cart')->where("id=$id")->get();
		if(!$cart){
			return false;
		}
		$option = json_decode($cart['options'], true);
		$subtotal = $this->count_subtotal($cart['goods_id'], $quantity, $option);
		$data = array(
			'quantity' => $quantity,
			'subtotal' => $subtotal,
		);
		$this->db->table('cart')->where("id=$id")->update($data);
		return $subtotal;
	}
	
	public function count_subtotal($goods_id, $quantity, $option=array())
	{
		$base_price = $this->db->table('goods')->where("goods_id=$goods_id")->getval('price');
		if($option){
			$price_option = $this->model('goods')->get_price_option($goods_id);
			foreach($option as $k=>$v){
				$k = intval($k);
				$v = intval($v);
				$base_price += $price_option[$k]['option'][$v]['price'];
			}
		}
		return $this->model('common')->current_price($base_price*$quantity, 0);
	}
	
	//run after login/register
	public function turn_cart()
	{
		$user_id = $_SESSION['user_id'];
		$session_id = session_id();
		
		if(!$user_id || !$session_id){
			return false;
		}
		
		$this->db->table('cart')->where("user_id=$user_id and session_id<>'$session_id'")->delete();
		
		$data = array('user_id' => $user_id);
		$this->db->table('cart')->where("session_id='$session_id'")->update($data);
		return true;
	}
	
	//run when change currency
	public function change_cart_cur()
	{
		$where = $this->cart_condition();
		$list = $this->db->table('cart')->where($where)->getlist();
		$currency = $this->model('common')->current_cur();
		foreach($list as $k=>$v){
			$option = json_decode($v['options'], true);
			$subtotal = $this->count_subtotal($v['goods_id'], $v['quantity'], $option);
			$data = array(
				'currency' => $currency,
				'subtotal' => $subtotal,
			);
			$this->db->table('cart')->where("id=".$v['id'])->update($data);
		}
		return true;
	}
	
	public function check_goods($goods_id, $quantity, $option=array())
	{
		//status
		$where = $this->model('goods')->base_condition();
		$where .= " and goods_id=$goods_id";
		$goods = $this->db->table('goods')->where($where)->get();
		if(!$goods){
			return 0;
		}
		//stock
		if($quantity > $goods['stock']){
			echo $quantity . $goods['stock'];exit;
			$setting = &$this->model('common')->setting();
			if($setting['pre_sale'] == 0){
				return -1;
			}
		}
		//option
		$price_option = $this->model('goods')->get_price_option($goods_id);
		if($price_option){
			foreach($price_option as $k=>$v){
				if(!$option[$k]){
					return -2;
				}else{
					$id = $option[$k];
					if(!$v['option'][$id]){
						return -2;
					}
				}
			}
		}
		return 1;
	}
	
	public function delete($id)
	{
		$where = $this->cart_condition();
		$where .= " and id='$id'";
		$cart = $this->db->table('cart')->where($where)->get();
		if(!$cart){
			return false;
		}else{
			$this->db->table('cart')->where("id='$id'")->delete();
			return true;
		}
	}
	
	public function truncate()
	{
		$where = $this->cart_condition();
		return $this->db->table('cart')->where($where)->delete();
	}
	
	public function clear()
	{
		//clear cart
		$time = time() - 3600*24;
		$this->db->table('cart')->where("user_id=0 and addtime<$time")->delete();
	}
}
?>