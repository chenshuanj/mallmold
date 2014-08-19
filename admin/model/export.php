<?php
/*
*	@export.php
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

class export extends model
{
	public function product($fields, $where='')
	{
		$groups = $this->model('goods')->groups(0);
		$cates = $this->model('goodscate')->getcates();
		$attributes = $this->model('goods')->attributes();
		$extensions = $this->model('goods')->extensions();
		$options = $this->model('goods')->options();
		
		$total = $this->db->table('goods')->where($where)->count();
		$n = ceil($total/50);
		$i = 1;
		$export_data = array();
		while($i <= $n){
			$limit = (($i - 1)*50).',50';
			$list = $this->model('mdata')->table('goods')->where($where)->order('addtime desc')->limit($limit)->getlist();
			foreach($list as $goods){
				$row = array();
				$goods_id = $goods['goods_id'];
				
				foreach($fields as $key=>$field){
					if(isset($goods[$field])){
						$row[$field] = $goods[$field];
					}
				}
				
				//group
				if(in_array('group', $fields)){
					$row['group'] = $groups[$goods['group_id']];
				}
				
				//category
				if(in_array('category', $fields)){
					$catelist = $this->db->table('goods_cate_val')->where("goods_id=$goods_id")->getlist();
					$row['category'] = array();
					foreach($catelist as $v){
						$row['category'][] = $cates[$v['cate_id']];
					}
				}
				
				//images
				if(in_array('images', $fields) || in_array('images_label', $fields)){
					$row['images'] = $row['images_label'] =  array();
					$images = $this->model('mdata')->table('goods_image')->where("goods_id=$goods_id")->getlist();
					foreach($images as $image){
						$row['images'][] = $image['image'];
						$row['images_label'][] = $image['label'];
					}
				}
				
				//crosssell
				if(in_array('crosssell', $fields)){
					$row['crosssell'] = $this->model('goods')->getcrosssell($goods_id);
				}
				
				//options
				if(in_array('options', $fields) || in_array('option_names', $fields) || in_array('option_prices', $fields)){
					$row['options'] = $row['option_names'] = $row['option_prices'] = array();
					$optionlist = $this->model('mdata')->table('goods_option')->where("goods_id=$goods_id")->order('sort_order asc')->getlist();
					foreach($optionlist as $v){
						$row['options'][] = $options[$v['op_id']];
						$row['option_names'][] = $v['name'];
						$row['option_prices'][] = $v['price'];
					}
				}
				
				//extends
				foreach($extensions as $extend){
					$code = 'extend_'.$extend['code'];
					if(in_array($code, $fields)){
						$row[$code] = $this->model('goods')->get_extend($extend, $goods_id);
					}
				}
				
				//attributes
				foreach($attributes as $attribute){
					$code = 'attribute_'.$attribute['code'];
					if(in_array($code, $fields)){
						$row[$code] = $this->model('goods')->get_attribute($attribute, $goods_id);
					}
				}
				
				$export_data[] = $row;
			}
			
			$i++;
		}
		
		return $export_data;
	}
	
	public function customer($fields, $where='')
	{
		$groups = $this->model('customer')->groups();
		$countries = $this->model('customer')->countries();
		$regions = $this->model('customer')->regions();
		
		$total = $this->db->table('user')->where($where)->count();
		$n = ceil($total/50);
		$i = 1;
		$export_data = array();
		while($i <= $n){
			$limit = (($i - 1)*50).',50';
			$list = $this->db->table('user')->where($where)->limit($limit)->getlist();
			foreach($list as $user){
				$row = array();
				$user_id = $user['user_id'];
				
				foreach($fields as $key=>$field){
					if(isset($user[$field])){
						if($field == 'reg_time'){
							$row[$field] = date('Y-m-d H:i:s', $user[$field]);
						}else{
							$row[$field] = $user[$field];
						}
					}
				}
				
				//group
				if(in_array('group', $fields)){
					$row['group'] = $groups[$user['group_id']];
				}
				
				$address = $this->db->table('user_address')->where("user_id=$user_id")->get();
				if($address){
					foreach($fields as $key=>$field){
						if(isset($address[$field])){
							$row[$field] = $address[$field];
						}
					}
					
					if(in_array('country', $fields)){
						$row['country'] = $countries[$address['country_id']];
					}
					
					if(in_array('region', $fields)){
						$row['region'] = $regions[$address['region_id']];
					}
					
					if(in_array('bill_country', $fields)){
						$row['bill_country'] = $countries[$address['bill_country_id']];
					}
					
					if(in_array('bill_region', $fields)){
						$row['bill_region'] = $regions[$address['bill_region_id']];
					}
				}
				
				$export_data[] = $row;
			}
			
			$i++;
		}
		
		return $export_data;
	}
	
	public function order($fields, $where='')
	{
		$shipments = $this->model('order')->shipments();
		$payments = $this->model('order')->payments();
		$order_status = $this->model('order')->order_status();
		$shipping_status = $this->model('order')->shipping_status();
		$countries = $this->model('customer')->countries();
		$regions = $this->model('customer')->regions();
		
		$total = $this->db->table('order')->where($where)->count();
		$n = ceil($total/50);
		$i = 1;
		$export_data = array();
		while($i <= $n){
			$limit = (($i - 1)*50).',50';
			$list = $this->db->table('order')->where($where)->limit($limit)->getlist();
			foreach($list as $order){
				$row = array();
				$order_id = $order['order_id'];
				
				foreach($fields as $key=>$field){
					if(isset($order[$field])){
						if($field == 'status'){
							$row[$field] = $order_status[$order['status']];
						}elseif($field == 'shipping_status'){
							$row[$field] = $shipping_status[$order['shipping_status']];
						}else{
							$row[$field] = $order[$field];
						}
					}
				}
				
				if(in_array('shipment', $fields)){
					$row['shipment'] = $shipments[$order['shipping_id']];
				}
				
				if(in_array('payment', $fields)){
					$row['payment'] = $payments[$order['payment_id']];
				}
				
				if(in_array('coupon', $fields)){
					$row['coupon'] = $this->db->table('coupon')->where("id=".$order['coupon_id'])->getval('code');
				}
				
				if(in_array('time', $fields)){
					$row['time'] = date('Y-m-d H:i:s', $order['addtime']);
				}
				
				if($this->model('importexport')->find_field($fields, 'goods_')){
					$row['goods_sku'] = $row['goods_name'] = $row['goods_options'] = $row['goods_price'] = $row['goods_quantity'] = $row['goods_subtotal'] = array();
					$order_goods = $this->db->table('order_goods')->where("order_id=$order_id")->getlist();
					foreach($order_goods as $goods){
						$row['goods_sku'][] = $goods['goods_sku'];
						$row['goods_name'][] = $goods['goods_name'];
						
						if(in_array('goods_options', $fields)){
							$options = $this->model('order')->format_options($goods['goods_options']);
							$row['goods_options'][] = $options ? json_encode($options) : '';
						}
						
						$row['goods_price'][] = $goods['price'];
						$row['goods_quantity'][] = $goods['quantity'];
						$row['goods_subtotal'][] = $goods['subtotal'];
					}
				}
				
				if($this->model('importexport')->find_field($fields, 'bill_')){
					$billing_address = $this->db->table('order_billing_address')->where("order_id=$order_id")->get();
					foreach($billing_address as $key=>$value){
						$field = 'bill_'.$key;
						if(isset($fields[$field])){
							$row[$field] = $value;
						}
					}
					
					if(in_array('bill_country', $fields)){
						$row['bill_country'] = $countries[$billing_address['country_id']];
					}
					
					if(in_array('bill_region', $fields)){
						$row['bill_region'] = $countries[$billing_address['region_id']];
					}
				}
				
				if($this->model('importexport')->find_field($fields, 'ship_')){
					$shipping_address = $this->db->table('order_shipping_address')->where("order_id=$order_id")->get();
					foreach($shipping_address as $key=>$value){
						$field = 'ship_'.$key;
						if(isset($fields[$field])){
							$row[$field] = $value;
						}
					}
					
					if(in_array('ship_country', $fields)){
						$row['ship_country'] = $countries[$shipping_address['country_id']];
					}
					
					if(in_array('ship_region', $fields)){
						$row['ship_region'] = $countries[$shipping_address['region_id']];
					}
				}
				
				$export_data[] = $row;
			}
			
			$i++;
		}
		
		return $export_data;
	}
	
	public function attribute($fields)
	{
		$types = array(
			1 => lang('Single'),
			2 => lang('Multiple'),
		);
		
		$export_data = array();
		$list = $this->model('mdata')->table('attribute')->getlist();
		foreach($list as $attribute){
			$row = array();
			$attr_id = $attribute['attr_id'];
			
			foreach($fields as $field){
				if($field == 'type'){
					$row[$field] = $types[$attribute['type']];
				}else{
					$row[$field] = $attribute[$field];
				}
			}
			
			if(in_array('values', $fields)){
				$row['values'] = $row['values_id'] = $row['values_sort_order'] = array();
				$values = $this->model('mdata')->table('attribute_value')->where("attr_id=$attr_id")->getlist();
				foreach($values as $value){
					$row['values'][] = $value['title'];
					$row['values_id'][] = $value['av_id'];
					$row['values_sort_order'][] = $value['sort_order'];
				}
			}
			
			$export_data[] = $row;
		}
		
		return $export_data;
	}
	
	public function extend($fields, $where='')
	{
		$types = $this->model('extend')->type();
		$export_data = array();
		$list = $this->model('mdata')->table('extend')->getlist();
		foreach($list as $extend){
			$row = array();
			$extend_id = $extend['extend_id'];
			
			foreach($fields as $field){
				if($field == 'type'){
					$row[$field] = $types[$extend['type']];
				}else{
					$row[$field] = $extend[$field];
				}
			}
			
			if(($extend['type'] == 2 || $extend['type'] == 3) && in_array('values', $fields)){
				$row['values'] = $row['values_id'] = $row['values_sort_order'] = array();
				$values = $this->model('mdata')->table('extend_val')->where("extend_id=$extend_id")->getlist();
				foreach($values as $value){
					$row['values'][] = $value['val'];
					$row['values_id'][] = $value['id'];
					$row['values_sort_order'][] = $value['sort_order'];
				}
			}
			
			$export_data[] = $row;
		}
		
		return $export_data;
	}
}
?>