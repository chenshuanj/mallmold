<?php
/*
*	@import.php
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

class import extends model
{
	public $error = array();
	public $result = array();
	
	public function product_check($data)
	{
		if(!isset($data[0]['sku'])){
			$this->error[0][] = 'sku is required';
			return false;
		}
		
		$groups = $this->model('goods')->groups(0);
		$cates = $this->model('goodscate')->getcates();
		$attributes = $this->model('goods')->attributes();
		$extensions = $this->model('goods')->extensions();
		$options = $this->model('goods')->options();
		
		foreach($data as $n=>$row){
			$l = $n+1;
			if(!$row['sku']){
				$this->error[$l][] = 'Sku should not be null';
			}
			
			if(isset($row['group']) && !in_array($row['group'], $groups)){
				$this->error[$l][] = 'Invalid group['.$row['group'].']';
			}
			
			if(isset($row['title']) && !$row['title']){
				$this->error[$l][] = 'Title should not be null';
			}
			
			if(isset($row['urlkey']) && !$row['urlkey']){
				$this->error[$l][] = 'Urlkey should not be null';
			}
			
			//category
			if($row['category']){
				if(!is_array($row['category'])){
					$row['category'] = array($row['category']);
				}
				
				$invalid = array();
				foreach($row['category'] as $category){
					if(!in_array($category, $cates)){
						$invalid[] = $category;
					}
				}
				
				if($invalid){
					$this->error[$l][] = 'Invalid category['.implode(', ', $invalid).']';
				}
			}
			
			//image
			if($row['image']){
				if(!file_exists(BASE_PATH .$row['image'])){
					$this->error[$l][] = 'Image does not exist['.$row['image'].']';
				}
			}
			
			//images
			if($row['images']){
				if(!is_array($row['images'])){
					$row['images'] = array($row['images']);
				}
				
				$invalid = array();
				foreach($row['images'] as $image){
					if(!file_exists(BASE_PATH .$image)){
						$invalid[] = $image;
					}
				}
				
				if($invalid){
					$this->error[$l][] = 'Images does not exist['.implode(', ', $invalid).']';
				}
			}
			
			//options
			if($row['options']){
				if(!is_array($row['options'])){
					$row['options'] = array($row['options']);
				}
				
				$invalid = array();
				foreach($row['options'] as $option){
					if(!in_array($option, $options)){
						$invalid[] = $option;
					}
				}
				
				if($invalid){
					$this->error[$l][] = 'Invalid option['.implode(', ', $invalid).']';
				}
			}
			
			//attributes
			foreach($attributes as $attribute){
				$code = 'attribute_'.$attribute['code'];
				if($row[$code]){
					if($attribute['type'] == 1 && is_array($row[$code])){
						$this->error[$l][] = 'Invalid filtering attribute['.implode($this->model('importexport')->delimiter, $row[$code]).']';
					}else{
						if(!is_array($row[$code])){
							$row[$code] = array($row[$code]);
						}
						
						$invalid = array();
						foreach($row[$code] as $attr){
							if(!in_array($attr, $attribute['values'])){
								$invalid[] = $attr;
							}
						}
						
						if($invalid){
							$this->error[$l][] = 'Invalid filtering attribute['.implode(', ', $invalid).']';
						}
					}
				}
			}
			
			//extensions
			foreach($extensions as $extend){
				$code = 'extend_'.$extend['code'];
				if($row[$code]){	
					if($extend['type'] == 2 || $extend['type'] == 3){
						if(!is_array($row[$code])){
							$row[$code] = array($row[$code]);
						}
						
						$invalid = array();
						foreach($row[$code] as $attr){
							if(!in_array($attr, $extend['values'])){
								$invalid[] = $attr;
							}
						}
						
						if($invalid){
							$this->error[$l][] = 'Invalid filtering attribute['.implode(', ', $invalid).']';
						}
					}elseif(is_array($row[$code])){
						$this->error[$l][] = 'Invalid extend attribute['.implode($this->model('importexport')->delimiter, $row[$code]).']';
					}
				}
			}
		}
		
		return $this->error ? false : true;
	}
	
	public function product($data)
	{
		$groups = array_flip($this->model('goods')->groups(0));
		$cates = array_flip($this->model('goodscate')->getcates());
		$options = array_flip($this->model('goods')->options());
		$attributes = $this->model('goods')->attributes();
		$extensions = $this->model('goods')->extensions();
		
		foreach($data as $n=>$row){
			$l = $n+1;
			$sku = $row['sku'];
			$goods = $this->model('mdata')->table('goods')->where("sku='$sku'")->get();
			if(!$goods){
				$goods = array(
					'group_id' => 0,
					'title' => '',
					'title_key_' => '',
					'urlkey' => '',
					'sku' => $sku,
					'price' => 0,
					'price_origin' => 0,
					'weight' => 0,
					'brief_txtkey_' => '',
					'brief' => '',
					'description_txtkey_' => '',
					'description' => '',
					'meta_title_key_' => '',
					'meta_title' => '',
					'meta_keywords_txtkey_' => '',
					'meta_keywords' => '',
					'meta_description_txtkey_' => '',
					'meta_description' => '',
					'image' => '',
					'stock' => 0,
					'is_sale' => 1,
					'sort_order' => 0,
				);
			}
			
			foreach($goods as $key=>$val){
				if($key == 'group_id' && isset($row['group'])){
					$goods['group_id'] = isset($groups[$row['group']]) ? intval($groups[$row['group']]) : 0;
				}elseif(isset($row[$key])){
					if(in_array($key, array('price','price_origin','weight'))){
						$row[$key] = floatval($row[$key]);
					}elseif(in_array($key, array('stock','is_sale','sort_order'))){
						$row[$key] = intval($row[$key]);
					}
					
					$goods[$key] = $row[$key];
				}
			}
			
			$goods_id = isset($goods['goods_id']) ? $goods['goods_id'] : 0;
			if($goods_id > 0){
				$this->model('mdata')->table('goods')->where("goods_id=$goods_id")->save($goods);
				$this->result[$l] = '['.$sku.'] Updated';
			}else{
				$goods['addtime'] = time();
				$goods_id = $this->model('mdata')->table('goods')->add($goods);
				if($goods_id < 1){
					$this->error[$l][] = lang('add_error');
					continue;
				}
				$this->result[$l] = '['.$sku.'] Inserted';
			}
			
			//category
			if(isset($row['category'])){
				if(!is_array($row['category'])){
					$row['category'] = array($row['category']);
				}
				
				$cate_ids = array();
				foreach($row['category'] as $category){
					$cate_ids[] = $cates[$category];
				}
				
				$this->model('goods')->update_cate($goods_id, $cate_ids);
			}
			
			//images
			if(isset($row['images'])){
				if(!is_array($row['images'])){
					$row['images'] = array($row['images']);
				}
				if(isset($row['images_label'])){
					if(!is_array($row['images_label'])){
						$row['images_label'] = array($row['images_label']);
					}
				}
				
				$list = $this->db->table('goods_image')->where("goods_id=$goods_id")->getlist();
				foreach($list as $k=>$v){
					if(in_array($v['image'], $row['images'])){
						if(isset($row['images_label']) && $row['images_label'][$k] != $v['label']){
							$goods_image = array(
								'label_key_'=> $v['label_key_'],
								'label'=> $row['images_label'][$k]
							);
							$this->model('mdata')->table('goods_image')->where("id=".$v['id'])->save($goods_image);
						}
						unset($row['images'][$k]);
						if(isset($row['images_label'][$k])){
							unset($row['images_label'][$k]);
						}
					}else{
						$this->model('mdata')->table('goods_image')->where("id=".$v['id'])->delete();
					}
				}
				
				if($row['images']){
					foreach($row['images'] as $k=>$image){
						$goods_image = array(
							'goods_id' => $goods_id,
							'image' => $image,
							'label_key_' => '',
							'label' => (isset($row['images_label']) ? $row['images_label'][$k] : ''),
						);
						$this->model('mdata')->table('goods_image')->add($goods_image);
					}
				}
			}
			
			//options
			if(isset($row['options']) && isset($row['option_names'])){
				if(!is_array($row['options'])){
					$row['options'] = array($row['options']);
				}
				if(!is_array($row['option_names'])){
					$row['option_names'] = array($row['option_names']);
				}
				if(isset($row['option_prices'])){
					if(!is_array($row['option_prices'])){
						$row['option_prices'] = array($row['option_prices']);
					}
				}else{
					$row['option_prices'] = array();
				}
				
				$goods_option = array();
				$optionlist = $this->model('mdata')->table('goods_option')->where("goods_id=$goods_id")->getlist();
				if($optionlist){
					foreach($optionlist as $v){
						$op_id = $v['op_id'];
						$name = $v['name'];
						$goods_option[$op_id][$name] = $v;
					}
				}
				
				foreach($row['options'] as $k=>$option){
					$op_id = $options[$option];
					$name = $row['option_names'][$k];
					if($op_id && $name){
						if($goods_option[$op_id][$name]){
							if(isset($row['option_prices'][$k]) && $row['option_prices'][$k] != $goods_option[$op_id][$name]['price']){
								$update = array(
									'price' => floatval($row['option_prices'][$k]),
									'sort_order' => $k,
								);
								$id = $goods_option[$op_id][$name]['id'];
								$this->db->table('goods_option')->where("id=$id")->update($update);
							}
							unset($goods_option[$op_id][$name]);
						}else{
							$update = array(
								'goods_id' => $goods_id,
								'op_id' => $op_id,
								'name_key_' => '',
								'name' => $name,
								'image' => '',
								'price' => floatval($row['option_prices'][$k]),
								'sort_order' => $k,
							);
							$this->model('mdata')->table('goods_option')->add($update);
						}
					}
				}
				
				if($goods_option){
					foreach($goods_option as $op_id=>$val){
						if($val){
							foreach($val as $v){
								$id = $v['id'];
								$this->model('mdata')->table('goods_option')->where("id=$id")->delete();
							}
						}
					}
				}
			}
			
			//attributes
			foreach($attributes as $attribute){
				$code = 'attribute_'.$attribute['code'];
				if(isset($row[$code])){
					$attr_id = $attribute['attr_id'];
					$this->db->table('goods_attr')->where("goods_id=$goods_id and attr_id=$attr_id")->delete();
					
					if(!$row[$code]){
						continue;
					}
					
					$values = array_flip($attribute['values']);
					if(!is_array($row[$code])){
						$row[$code] = array($row[$code]);
					}
					
					foreach($row[$code] as $value){
						$av_id = $values[$value];
						$this->db->table('goods_attr')->insert(
							array('goods_id'=>$goods_id, 'attr_id'=>$attr_id, 'av_id'=>$av_id)
						);
					}
				}
			}
			
			
			//extensions
			foreach($extensions as $extend){
				$code = 'extend_'.$extend['code'];
				if(isset($row[$code])){
					$extend_id = $extend['extend_id'];
					$val = '';
					if(!$row[$code]){
						$this->db->table('goods_extend')->where("goods_id=$goods_id and extend_id=$extend_id")->delete();
					}elseif($extend['type'] == 2 || $extend['type'] == 3){ //selection
						$values = array_flip($extend['values']);
						if(!is_array($row[$code])){
							$row[$code] = array($row[$code]);
						}
						
						$value_ids = array();
						foreach($row[$code] as $value){
							$value_ids[] = $values[$value];
						}
						$val = implode(',', $value_ids);
					}elseif($extend['type'] == 4){ //bool
						$val = abs(intval($row[$code]));
						$val > 1 && $val = 1;
					}else{
						$val = trim($row[$code]);
					}
					
					if($val){
						$this->db->table('goods_extend')->where("goods_id=$goods_id and extend_id=$extend_id")->update(array('val' => $val));
					}
				}
			}
			
			//crosssell
			if($row['crosssell']){
				if(!is_array($row['crosssell'])){
					$row['crosssell'] = array($row['crosssell']);
				}
				
				$this->model('goods')->setcrosssell($goods_id, $row['crosssell']);
			}
		}
		
		return $this->error ? false : true;
	}
	
	public function customer_check($data)
	{
		if(!isset($data[0]['email'])){
			$this->error[0][] = 'email is required';
			return false;
		}
		
		$groups = $this->model('customer')->groups();
		$languages = $this->model('customer')->languages();
		$countries = $this->model('customer')->countries();
		$regions = $this->model('customer')->regions();
		
		foreach($data as $n=>$row){
			$l = $n+1;
			if(!$row['email']){
				$this->error[$l][] = 'Email should not be null';
			}
			
			if(isset($row['firstname']) && !$row['firstname']){
				$this->error[$l][] = 'Firstname should not be null';
			}
			
			if(isset($row['lastname']) && !$row['lastname']){
				$this->error[$l][] = 'Lastname should not be null';
			}
			
			if(isset($row['group']) && !in_array($row['group'], $groups)){
				$this->error[$l][] = 'Invalid group['.$row['group'].']';
			}
			
			if(isset($row['password'])){
				if(!$row['password']){
					$this->error[$l][] = 'Password should not be null';
				}
				if(!$row['salt']){
					$this->error[$l][] = 'Salt should not be null';
				}
			}
			
			if(isset($row['language']) && !in_array($row['language'], $languages)){
				$this->error[$l][] = 'Invalid language['.$row['language'].']';
			}
			
			if(isset($row['country']) && !in_array($row['country'], $countries)){
				$this->error[$l][] = 'Invalid country['.$row['country'].']';
			}
			
			if(isset($row['bill_country']) && !in_array($row['bill_country'], $countries)){
				$this->error[$l][] = 'Invalid bill_country['.$row['bill_country'].']';
			}
			
			if(isset($row['region']) && !in_array($row['region'], $regions)){
				$this->error[$l][] = 'Invalid region['.$row['region'].']';
			}
			
			if(isset($row['bill_region']) && !in_array($row['bill_region'], $regions)){
				$this->error[$l][] = 'Invalid bill_region['.$row['bill_region'].']';
			}
		}
		
		return $this->error ? false : true;
	}
	
	public function customer($data)
	{
		$groups = array_flip($this->model('customer')->groups());
		$languages = $this->model('customer')->languages();
		$countries = array_flip($this->model('customer')->countries());
		$regions = array_flip($this->model('customer')->regions());
		
		foreach($data as $n=>$row){
			$l = $n+1;
			$email = $row['email'];
			$user = $this->db->table('user')->where("email='$email'")->get();
			if(!$user){
				$user = array(
					'group_id' => 1,
					'firstname' => 0,
					'lastname' => 0,
					'email' => $email,
					'password' => 0,
					'salt' => 0,
					'language' => 0,
					'reg_time' => time(),
					'login_time' => 0,
					'status' => 1,
				);
			}
			
			foreach($user as $key=>$val){
				if($key == 'group_id' && isset($row['group'])){
					$user['group_id'] = isset($groups[$row['group']]) ? intval($groups[$row['group']]) : 1;
				}elseif($key == 'reg_time' && isset($row['reg_time'])){
					$row[$key] = strtotime($row['reg_time']);
				}elseif(isset($row[$key])){
					if($key == 'status'){
						$row[$key] = intval($row[$key]);
					}
					$user[$key] = $row[$key];
				}
			}
			
			$user_id = isset($user['user_id']) ? $user['user_id'] : 0;
			if($user_id > 0){
				$this->db->table('user')->where("user_id=$user_id")->update($user);
				$this->result[$l] = '['.$email.'] Updated';
			}else{
				$user_id = $this->db->table('user')->insert($user);
				if($user_id < 1){
					$this->error[$l][] = lang('add_error');
					continue;
				}
				$this->result[$l] = '['.$email.'] Inserted';
			}
			
			$address = $this->db->table('user_address')->where("user_id=$user_id")->order('id asc')->get();
			if(!$address){
				$address = array(
					'user_id' => $user_id,
					'firstname' => '',
					'lastname' => '',
					'country_id' => null,
					'region_id' => null,
					'city' => '',
					'address' => '',
					'address2' => '',
					'phone' => '',
					'postcode' => '',
					
					'bill_firstname' => '',
					'bill_lastname' => '',
					'bill_country_id' => null,
					'bill_region_id' => null,
					'bill_city' => '',
					'bill_address' => '',
					'bill_address2' => '',
					'bill_phone' => '',
					'bill_postcode' => '',
				);
			}
			
			foreach($address as $key=>$value){
				if(isset($row[$key])){
					$address[$key] = $row[$key];
				}
			}
			
			if(isset($row['country'])){
				$address['country_id'] = $countries[$row['country']];
			}
			if(isset($row['region'])){
				$address['region_id'] = $regions[$row['region']];
			}
			if(isset($row['bill_country'])){
				$address['bill_country_id'] = $countries[$row['bill_country']];
			}
			if(isset($row['bill_region'])){
				$address['bill_region_id'] = $regions[$row['bill_region']];
			}
			
			$status = true;
			foreach($address as $k=>$v){
				if(!in_array($k, array('lastname','address2', 'bill_lastname', 'bill_address2')) && !$v){
					$this->result[$l] .= '; ['.$k.'] is null, skipped address update';
					$status = false;
					break;
				}
			}
			
			if($status){
				$id = isset($address['id']) ? $address['id'] : 0;
				if($id > 0){
					$this->db->table('user_address')->where("id=$id")->update($address);
				}else{
					$this->db->table('user_address')->insert($address);
				}
			}
		}
		
		return $this->error ? false : true;
	}
	
	public function order_check($data)
	{
		$check = array(
			'order_sn','invoice','email','goods_amount','total_amount','shipment','payment','currency','language',
			'goods_sku','goods_price','goods_quantity', 'time', 'status', 'shipping_status',
			'bill_firstname', 'bill_lastname', 'bill_country', 'bill_region', 'bill_city', 'bill_address', 'bill_postcode',
			'ship_firstname', 'ship_lastname', 'ship_country', 'ship_region', 'ship_city', 'ship_address', 'ship_postcode',
		);
		
		$required = array();
		foreach($check as $field){
			if(!isset($data[0][$field])){
				$required[] = $field;
			}
		}
		
		if($required){
			$this->error[0][] = implode(',', $required) .' is required';
			return false;
		}
		
		$languages = $this->model('customer')->languages();
		$countries = $this->model('customer')->countries();
		$regions = $this->model('customer')->regions();
		$order_status = $this->model('order')->order_status();
		$shipping_status = $this->model('order')->shipping_status();
		$shipments = $this->model('order')->shipments();
		$payments = $this->model('order')->payments();
		$currencies = $this->model('order')->currencies();
		
		foreach($data as $n=>$row){
			$l = $n+1;
			
			foreach($check as $field){
				if(strlen($row[$field]) == 0){
					$this->error[$l][] = $field.' should not be null';
				}
			}
			
			if($row['shipment'] && !in_array($row['shipment'], $shipments)){
				$this->error[$l][] = 'Invalid shipment['.$row['shipment'].']';;
			}
			if($row['payment'] && !in_array($row['payment'], $payments)){
				$this->error[$l][] = 'Invalid payment['.$row['payment'].']';;
			}
			if($row['currency'] && !$currencies[$row['currency']]){
				$this->error[$l][] = 'Invalid currency['.$row['currency'].']';;
			}
			if($row['language'] && !in_array($row['language'], $languages)){
				$this->error[$l][] = 'Invalid language['.$row['language'].']';;
			}
			if($row['status'] && !in_array($row['status'], $order_status)){
				$this->error[$l][] = 'Invalid status['.$row['status'].']';;
			}
			if($row['shipping_status'] && !in_array($row['shipping_status'], $shipping_status)){
				$this->error[$l][] = 'Invalid shipping_status['.$row['shipping_status'].']';;
			}
			if($row['bill_country'] && !in_array($row['bill_country'], $countries)){
				$this->error[$l][] = 'Invalid bill_country['.$row['bill_country'].']';
			}
			if($row['ship_country'] && !in_array($row['ship_country'], $countries)){
				$this->error[$l][] = 'Invalid ship_country['.$row['ship_country'].']';
			}
			if($row['bill_region'] && !in_array($row['bill_region'], $regions)){
				$this->error[$l][] = 'Invalid bill_region['.$row['bill_region'].']';
			}
			if($row['ship_region'] && !in_array($row['ship_region'], $regions)){
				$this->error[$l][] = 'Invalid ship_region['.$row['ship_region'].']';
			}
		}
		
		return $this->error ? false : true;
	}
	
	public function order($data)
	{
		$countries = array_flip($this->model('customer')->countries());
		$regions = array_flip($this->model('customer')->regions());
		$order_status = array_flip($this->model('order')->order_status());
		$shipping_status = array_flip($this->model('order')->shipping_status());
		$shipments = array_flip($this->model('order')->shipments());
		$payments = array_flip($this->model('order')->payments());
		
		foreach($data as $n=>$row){
			$l = $n+1;
			$order_sn = $row['order_sn'];
			$count = $this->db->table('order')->where("order_sn='$order_sn'")->count();
			if($count > 0){
				$this->result[$l] = '['.$order_sn.'] exist, skipped';
				continue;
			}
			
			$user_id = $this->db->table('user')->where("email='{$row['email']}'")->getval('user_id');
			if(!$user_id){
				$user_id = 0;
			}
			
			$coupon_id = 0;
			if($row['coupon']){
				$coupon_id = $this->db->table('coupon')->where("code='{$row['coupon']}'")->getval('coupon_id');
				$coupon_id = intval($coupon_id);
			}
			
			$order = array(
				'order_sn' => $order_sn,
				'invoice' => $row['invoice'],
				'user_id' => $user_id,
				'email' => $row['email'],
				'shipping_id' => $shipments[$row['shipment']],
				'payment_id' => $payments[$row['payment']],
				'coupon_id' => $coupon_id,
				'currency' => $row['currency'],
				'language' => $row['language'],
				'goods_amount' => floatval($row['goods_amount']),
				'shipping_fee' => floatval($row['shipping_fee']),
				'tax_fee' => floatval($row['tax_fee']),
				'total_amount' => floatval($row['total_amount']),
				'gift' => floatval($row['gift']),
				'addtime' => strtotime($row['time']),
				'status' => $order_status[$row['status']],
				'shipping_status' => $shipping_status[$row['shipping_status']],
				'refund' => floatval($row['refund']),
			);
			$order_id = $this->db->table('order')->insert($order);
			if(!$order_id){
				$this->error[$l][] = '['.$order_sn.'] '.lang('add_error');
				continue;
			}else{
				$this->result[$l] = '['.$order_sn.'] Inserted';
			}
			
			//save goods
			if(!is_array($row['goods_sku'])){
				$row['goods_sku'] = array($row['goods_sku']);
			}
			if(!is_array($row['goods_name'])){
				$row['goods_name'] = array($row['goods_name']);
			}
			if(!is_array($row['goods_options'])){
				$row['goods_options'] = array($row['goods_options']);
			}
			if(!is_array($row['goods_price'])){
				$row['goods_price'] = array($row['goods_price']);
			}
			if(!is_array($row['goods_quantity'])){
				$row['goods_quantity'] = array($row['goods_quantity']);
			}
			if(!is_array($row['goods_subtotal'])){
				$row['goods_subtotal'] = array($row['goods_subtotal']);
			}
			foreach($row['goods_sku'] as $k=>$sku){
				$goods_id = $this->db->table('goods')->where("sku='$sku'")->getval('goods_id');
				$goods_data = array(
					'order_id' => $order_id,
					'goods_id' => intval($goods_id),
					'goods_sku' => $sku,
					'goods_name' => addslashes($row['goods_name'][$k]),
					'options' => addslashes($row['goods_options'][$k]),
					'price' => floatval($row['goods_price'][$k]),
					'quantity' => intval($row['goods_quantity'][$k]),
					'subtotal' => floatval($row['goods_subtotal'][$k]),
					'shipping' => 0,
				);
				$this->db->table('order_goods')->insert($goods_data);
			}
			
			//save shipping address
			$shipping_address = array(
				'order_id' => $order_id,
				'user_id' => $user_id,
				'firstname' => $row['ship_firstname'],
				'lastname' => $row['ship_lastname'],
				'country_id' => $countries[$row['ship_country']],
				'region_id' => $regions[$row['ship_region']],
				'city' => $row['ship_city'],
				'address' => $row['ship_address'],
				'address2' => $row['ship_address2'],
				'phone' => $row['ship_phone'],
				'postcode' => $row['ship_postcode'],
			);
			$this->db->table('order_shipping_address')->insert($shipping_address);
			
			//save billing address
			$billing_address = array(
				'order_id' => $order_id,
				'user_id' => $user_id,
				'firstname' => $row['bill_firstname'],
				'lastname' => $row['bill_lastname'],
				'country_id' => $countries[$row['bill_country']],
				'region_id' => $regions[$row['bill_region']],
				'city' => $row['bill_city'],
				'address' => $row['bill_address'],
				'address2' => $row['bill_address2'],
				'phone' => $row['bill_phone'],
				'postcode' => $row['bill_postcode'],
			);
			$this->db->table('order_billing_address')->insert($billing_address);
		}
		
		return $this->error ? false : true;
	}
	
	public function attribute_check($data)
	{
		if(!isset($data[0]['code'])){
			$this->error[0][] = 'code is required';
			return false;
		}
		
		$check = array('code', 'name', 'type');
		$types = array(
			1 => lang('Single'),
			2 => lang('Multiple'),
		);
		
		foreach($data as $n=>$row){
			$l = $n+1;
			
			foreach($check as $field){
				if(strlen($row[$field]) == 0){
					$this->error[$l][] = $field.' should not be null';
				}elseif($field == 'type' && !in_array($row[$field], $types)){
					$this->error[$l][] = 'Invalid type['.$row['type'].']';
				}
			}
		}
		
		return $this->error ? false : true;
	}
	
	public function attribute($data)
	{
		$types = array_flip(array(
			1 => lang('Single'),
			2 => lang('Multiple'),
		));
		
		foreach($data as $n=>$row){
			$l = $n+1;
			$code = $row['code'];
			$attribute = $this->db->table('attribute')->where("code='$code'")->get();
			if(!$attribute){
				$attribute = array(
					'code' => $code,
					'name_key_' => '',
					'name' => trim($row['name']),
					'type' => $types[$row['type']],
					'can_filter' => ($row['can_filter'] ? 1 : 0),
					'sort_order' => intval($row['sort_order']),
					'status' => ($row['status'] ? 1 : 0),
				);
			}
			if($attribute['attr_id']){
				$attr_id = $attribute['attr_id'];
				$this->model('mdata')->table('attribute')->where("attr_id=$attr_id")->save($attribute);
				$this->result[$l] = '['.$code.'] Updated';
			}else{
				$attr_id = $this->model('mdata')->table('attribute')->add($attribute);
				$this->result[$l] = '['.$code.'] Inserted';
			}
			
			if(isset($row['values'])){
				if(!is_array($row['values'])){
					$row['values'] = array($row['values']);
				}
				if(!is_array($row['values_id'])){
					$row['values_id'] = array($row['values_id']);
				}
				if(!is_array($row['values_sort_order'])){
					$row['values_sort_order'] = array($row['values_sort_order']);
				}
				
				$attribute_value = array();
				$values = $this->model('mdata')->table('attribute_value')->where("attr_id=$attr_id")->getlist();
				foreach($values as $value){
					$av_id = $value['av_id'];
					$attribute_value[$av_id] = $value['title'];
				}
				
				$update = array();
				foreach($row['values'] as $k=>$title){
					$id = intval($row['values_id'][$k]);
					$title = trim($title);
					$av_id = 0;
					
					if($id > 0 && $attribute_value[$id]){
						$av_id = $id;
					}elseif(in_array($title, $attribute_value)){
						$av_id = array_search($title, $attribute_value);
					}
					
					if($av_id){
						$value = $this->model('mdata')->table('attribute_value')->where("attr_id=$attr_id and av_id=$av_id")->get();
					}else{
						$value = array(
							'attr_id' => $attr_id,
							'title_key_' => '',
							'title' => '',
							'sort_order' => 0,
						);
					}
					
					$value['title'] = $title;
					$value['sort_order'] = intval($row['values_sort_order'][$k]);
					
					if($av_id){
						$this->model('mdata')->table('attribute_value')->where("av_id=$av_id")->save($value);
					}else{
						$av_id = $this->model('mdata')->table('attribute_value')->add($value);
					}
					
					$update[] = $av_id;
				}
				
				$this->model('mdata')->table('attribute_value')->where("attr_id=$attr_id and av_id not in (".implode(',', $update).")")->delete();
				$this->db->table('goods_attr')->where("attr_id=$attr_id and av_id not in (".implode(',', $update).")")->delete();
			}
		}
		
		return $this->error ? false : true;
	}
	
	public function extend_check($data)
	{
		if(!isset($data[0]['code'])){
			$this->error[0][] = 'code is required';
			return false;
		}
		
		$check = array('code', 'name', 'type');
		$types = $this->model('extend')->type();
		
		foreach($data as $n=>$row){
			$l = $n+1;
			
			foreach($check as $field){
				if(strlen($row[$field]) == 0){
					$this->error[$l][] = $field.' should not be null';
				}elseif($field == 'type' && !in_array($row[$field], $types)){
					$this->error[$l][] = 'Invalid type['.$row['type'].']';
				}
			}
		}
		
		return $this->error ? false : true;
	}
	
	public function extend($data)
	{
		$types = array_flip($this->model('extend')->type());
		foreach($data as $n=>$row){
			$l = $n+1;
			$code = $row['code'];
			$extend = $this->db->table('extend')->where("code='$code'")->get();
			if(!$extend){
				$extend = array(
					'code' => $code,
					'name_key_' => '',
					'name' => trim($row['name']),
					'type' => $types[$row['type']],
					'sort_order' => intval($row['sort_order']),
					'status' => ($row['status'] ? 1 : 0),
				);
			}
			if($extend['extend_id']){
				$extend_id = $extend['extend_id'];
				$this->model('mdata')->table('extend')->where("extend_id=$extend_id")->save($extend);
				$this->result[$l] = '['.$code.'] Updated';
			}else{
				$extend_id = $this->model('mdata')->table('extend')->add($extend);
				$this->result[$l] = '['.$code.'] Inserted';
			}
			
			if(($extend['type'] == 2 || $extend['type'] == 3) && isset($row['values'])){
				if(!is_array($row['values'])){
					$row['values'] = array($row['values']);
				}
				if(!is_array($row['values_id'])){
					$row['values_id'] = array($row['values_id']);
				}
				if(!is_array($row['values_sort_order'])){
					$row['values_sort_order'] = array($row['values_sort_order']);
				}
				
				$extend_val = array();
				$values = $this->model('mdata')->table('extend_val')->where("extend_id=$extend_id")->getlist();
				foreach($values as $value){
					$id = $value['id'];
					$extend_val[$id] = $value['val'];
				}
				
				$update = array();
				foreach($row['values'] as $k=>$val){
					$id = intval($row['values_id'][$k]);
					$val = trim($val);
					$val_id = 0;
					
					if($id > 0 && $extend_val[$id]){
						$val_id = $id;
					}elseif(in_array($val, $extend_val)){
						$val_id = array_search($val, $extend_val);
					}
					
					if($av_id){
						$value = $this->model('mdata')->table('extend_val')->where("extend_id=$extend_id and id=$val_id")->get();
					}else{
						$value = array(
							'extend_id' => $extend_id,
							'val_key_' => '',
							'val' => '',
							'sort_order' => 0,
						);
					}
					
					$value['val'] = $val;
					$value['sort_order'] = intval($row['values_sort_order'][$k]);
					
					if($val_id){
						$this->model('mdata')->table('extend_val')->where("id=$val_id")->save($value);
					}else{
						$val_id = $this->model('mdata')->table('extend_val')->add($value);
					}
					
					$update[] = $val_id;
				}
				
				$this->model('mdata')->table('extend_val')->where("extend_id=$extend_id and id not in (".implode(',', $update).")")->delete();
				//goods_extend
			}
		}
		
		return $this->error ? false : true;
	}
}
?>