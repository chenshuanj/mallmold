<?php
/*
*	@importexport.php
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

class importexport extends model
{
	public $delimiter = '; ';
	
	public function get_fields($model)
	{
		switch($model){
			case 'product':
				$fields = $this->product_fields();
				break;
			case 'customer':
				$fields = $this->customer_fields();
				break;
			case 'order':
				$fields = $this->order_fields();
				break;
			case 'attribute':
				$fields = $this->attribute_fields();
				break;
			case 'extend':
				$fields = $this->extend_fields();
				break;
			default:
				$fields = $this->product_fields();
		}
		
		return $fields;
	}
	
	public function get_sign_field($model)
	{
		switch($model){
			case 'product':
				$field = 'sku';
				break;
			case 'customer':
				$field = 'email';
				break;
			case 'order':
				$field = 'order_sn';
				break;
			case 'attribute':
			case 'extend':
				$field = 'code';
				break;
			default:
				$field = 'sku';
		}
		
		return $field;
	}
	
	private function product_fields()
	{
		$fields = array(
			'sku', 'group', 'title', 'urlkey', 'price_origin', 'price', 'weight', 'brief', 'description',
			'meta_title', 'meta_keywords', 'meta_description', 'image', 'stock', 'sold_num', 'is_sale', 'sort_order', 'status',
			'category', 'images', 'images_label', 'crosssell', 'options', 'option_names', 'option_prices'
		);
		
		//extends
		$extends = $this->db->table('extend')->where("code<>'' and status=1")->getlist();
		foreach($extends as $extend){
			$fields[] = 'extend_'.$extend['code'];
		}
		
		//attributes
		$attributes = $this->db->table('attribute')->where("code<>'' and status=1")->getlist();
		foreach($attributes as $attribute){
			$fields[] = 'attribute_'.$attribute['code'];
		}
		
		return $fields;
	}
	
	private function customer_fields()
	{
		$fields = array(
			'email', 'password', 'salt', 'group', 'language', 'reg_time', 'status',
			'firstname', 'lastname', 'country', 'region', 'city', 'address', 'address2', 'phone', 'postcode',
			'bill_firstname', 'bill_lastname', 'bill_country', 'bill_region', 'bill_city', 'bill_address', 'bill_address2', 'bill_phone', 'bill_postcode',
		);
		
		return $fields;
	}
	
	private function order_fields()
	{
		$fields = array(
			'order_sn', 'invoice', 'email', 'shipment', 'payment', 'coupon', 'currency', 'language', 
			'goods_amount', 'shipping_fee', 'tax_fee', 'total_amount', 'gift', 'time', 'status', 'shipping_status', 'refund',
			'goods_sku', 'goods_name', 'goods_options', 'goods_price', 'goods_quantity', 'goods_subtotal', 
			'bill_firstname', 'bill_lastname', 'bill_country', 'bill_region', 'bill_city', 'bill_address', 'bill_address2', 'bill_postcode', 'bill_phone',
			'ship_firstname', 'ship_lastname', 'ship_country', 'ship_region', 'ship_city', 'ship_address', 'ship_address2', 'ship_postcode', 'ship_phone',
		);
		
		return $fields;
	}
	
	private function attribute_fields()
	{
		$fields = array(
			'code', 'name', 'type', 'can_filter', 'sort_order', 'status', 'values', 'values_id', 'values_sort_order'
		);
		
		return $fields;
	}
	
	private function extend_fields()
	{
		$fields = array(
			'code', 'name', 'type', 'sort_order', 'status', 'values', 'values_id', 'values_sort_order'
		);
		
		return $fields;
	}
	
	public function find_field(array $fields, $key)
	{
		$match = false;
		foreach($fields as $field){
			if(strpos($field, $key) !== false){
				$match = true;
				break;
			}
		}
		return $match;
	}
	
	public function get_mapping($data)
	{
		$mapping = array();
		if($data['fields'] == 1){
			$list = $this->db->table('importexport_mapping')->where("p_id=".$data['id'])->order('id asc')->getlist();
			foreach($list as $row){
				$key = $row['mapping_name'] ? $row['mapping_name'] : $row['field_name'];
				$mapping[$key] = $row['field_name'];
			}
		}else{
			$fields = $this->get_fields($data['model']);
			foreach($fields as $field){
				$mapping[$field] = $field;
			}
		}
		
		return $mapping;
	}
	
	public function file_format($export, $fields, $model_data)
	{
		if($export['format'] == 1){
			$model_data = $this->set_delimiter_format($model_data, $export['delimiter']);
		}else{
			$model_data = $this->set_row_format($model_data);
		}
		
		$file_data = $head = array();
		foreach($fields as $key=>$field){
			$head[] = $key;
		}
		$file_data[] = $head;
		foreach($model_data as $row){
			$data = array();
			foreach($fields as $field){
				$data[] = $row[$field];
			}
			$file_data[] = $data;
		}
		return $file_data;
	}
	
	private function set_row_format($model_data)
	{
		$model_data_new = array();
		foreach($model_data as $row){
			$n = 1;
			foreach($row as $value){
				if(is_array($value)){
					$n = max($n, count($value));
				}
			}
			
			for($i=0; $i<$n; $i++){
				$data = array();
				foreach($row as $key=>$value){
					if(is_array($value)){
						$data[$key] = (isset($value[$i]) ? $value[$i] : '');
					}else{
						$data[$key] = ($i == 0 ? $value : '');
					}
				}
				$model_data_new[] = $data;
			}
		}
		return $model_data_new;
	}
	
	private function set_delimiter_format($model_data, $delimiter)
	{
		if(!$delimiter){
			$delimiter = $this->delimiter;
		}
		
		foreach($model_data as $k=>$row){
			foreach($row as $key=>$value){
				if(is_array($value)){
					$model_data[$k][$key] = implode($delimiter, $value);
				}
			}
		}
		return $model_data;
	}
	
	public function get_import($import, $file)
	{
		$file_data = $this->load('lib/csv')->get($file);
		@unlink($file);
		
		if(!$file_data){
			return false;
		}
		
		if($import['format'] == 1){
			$file_data = $this->get_delimiter_format($file_data, $import['delimiter']);
		}else{
			$sign_field = $this->get_sign_field($import['model']);
			$file_data = $this->get_row_format($file_data, $sign_field);
		}
		
		$fields = $this->get_mapping($import);
		$import_data = array();
		foreach($file_data as $row){
			$data = array();
			foreach($fields as $key=>$field){
				$data[$field] = $row[$key];
			}
			$import_data[] = $data;
		}
		return $import_data;
	}
	
	private function get_row_format($file_data, $sign_field)
	{
		$file_data_new = array();
		$data = array();
		foreach($file_data as $row){
			if($row[$sign_field] && $data){
				$file_data_new[] = $data;
				$data = array();
			}
			
			foreach($row as $key=>$value){
				if($value){
					if(!$data[$key]){
						$data[$key] = $value;
					}elseif(is_array($data[$key])){
						$data[$key][] = $value;
					}else{
						$value_pre = $data[$key];
						$data[$key] = array($value_pre, $value);
					}
				}
			}
		}
		if($data){
			$file_data_new[] = $data;
		}
		return $file_data_new;
	}
	
	private function get_delimiter_format($file_data, $delimiter)
	{
		if(!$delimiter){
			$delimiter = $this->delimiter;
		}
		
		foreach($file_data as $k=>$row){
			foreach($row as $key=>$value){
				if(strpos($value, $delimiter) !== false){
					$file_data[$k][$key] = explode($delimiter, $value);
				}
			}
		}
		return $file_data;
	}
	
	public function out($file_data, $name)
	{
		$lang_code = cookie('admin_lang');
		$str = $this->load('lib/csv')->put($file_data);
		$name = $name.'_'.$lang_code.'.csv';
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$name);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . strlen($str));
		ob_clean();
		flush();
		echo $str;
		exit;
	}
}
?>