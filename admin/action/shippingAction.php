<?php
/*
*	@shippingAction.php
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

require Action('common');

class shippingAction extends commonAction
{
	public function index()
	{
		$country = array();
		$country_list = $this->db->table('country')->where('status=1')->getlist();
		foreach($country_list as $v){
			$country[$v['id']] = $v['name'];
		}
		
		$this->view['list'] = $this->db->table('shipping')->getlist();
		$this->view['country'] = $country;
		$this->view['title'] = lang('shipping');
		$this->view('shipping/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->db->table('shipping')->where("shipping_id=$id")->get();
				$this->view['data'] = $data;
			}
			$this->view['country_list'] = $this->db->table('country')->where('status=1')->getlist();
			$this->view('shipping/edit.html');
		}else{
			if(!$_POST['name']){
				$this->error('required_null');
			}
			
			$data = array(
				'name' => trim($_POST['name']),
				'country_id' => intval($_POST['country_id']),
				'base_weight' => intval($_POST['base_weight']),
				'base_fee' => floatval($_POST['base_fee']),
				'step_weight' => intval($_POST['step_weight']),
				'step_fee' => floatval($_POST['step_fee']),
				'sort_order' => intval($_POST['sort_order']),
				'status' => intval($_POST['status']),
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->db->table('shipping')->where("shipping_id=$id")->update($data);
			}else{
				$id = $this->db->table('shipping')->insert($data);
			}
			
			$this->ok('edit_success', url('shipping/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->db->table('shipping_set')->where("shipping_id=$id")->delete();
			$this->db->table('shipping')->where("shipping_id=$id")->delete();
		}
		$this->ok('delete_done', url('shipping/index'));
	}
	
	public function region()
	{
		$shipping_id = intval($_GET['shipping_id']);
		if(!$shipping_id){
			$this->error('args_error');
		}
		
		$this->view['list'] = $this->db->table('shipping_set')->where("shipping_id=$shipping_id")->getlist();
		$data = $this->db->table('shipping')->where("shipping_id=$shipping_id")->get();
		
		$country_id = $data['country_id'];
		$region = array();
		$region_list = $this->db->table('region')->where("country_id=$country_id")->getlist();
		foreach($region_list as $v){
			$region[$v['region_id']] = $v['name'];
		}
		
		$this->view['data'] = $data;
		$this->view['region'] = $region;
		$this->view['title'] = lang('region_area');
		$this->view('shipping/region.html');
	}
	
	public function editregion()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->db->table('shipping_set')->where("id=$id")->get();
				$shipping_id = $data['shipping_id'];
				$this->view['data'] = $data;
			}else{
				$shipping_id = intval($_GET['shipping_id']);
			}
			
			if(!$shipping_id){
				$this->error('args_error');
			}
			
			$country_id = $this->db->table('shipping')->where("shipping_id=$shipping_id")->getval('country_id');
			$this->view['region_list'] = $this->db->table('region')->where("country_id=$country_id")->getlist();
			$this->view['shipping_id'] = $shipping_id;
			$this->view('shipping/editregion.html');
		}else{
			if(!$_POST['shipping_id'] || !$_POST['region_id'] || !$_POST['base_fee']){
				$this->error('required_null');
			}
			
			$data = array(
				'shipping_id' => intval($_POST['shipping_id']),
				'region_id' => intval($_POST['region_id']),
				'base_fee' => floatval($_POST['base_fee']),
				'step_fee' => floatval($_POST['step_fee']),
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->db->table('shipping_set')->where("id=$id")->update($data);
			}else{
				//check repeat
				$where = "shipping_id=".$data['shipping_id']." and region_id=".$data['region_id'];
				$n = $this->db->table('shipping_set')->where($where)->count();
				if($n>0){
					$this->error('area_repeated');
				}else{
					$this->db->table('shipping_set')->insert($data);
				}
			}
			
			$this->ok('edit_success', url('shipping/region?shipping_id='.$data['shipping_id']));
		}
	}
	
	public function delregion()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$shipping_id = $this->db->table('shipping_set')->where("id=$id")->getval('shipping_id');
		$this->db->table('shipping_set')->where("id=$id")->delete();
		
		$this->ok('delete_done', url('shipping/region?shipping_id='.$shipping_id));
	}
}

?>