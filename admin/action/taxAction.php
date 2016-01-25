<?php
/*
*	@taxAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require Action('common');

class taxAction extends commonAction
{
	public function index()
	{
		$country = array();
		$country_list = $this->db->table('country')->where('status=1')->getlist();
		foreach($country_list as $v){
			$country[$v['id']] = $v['name'];
		}
		
		$this->view['list'] = $this->db->table('tax')->getlist();
		$this->view['country'] = $country;
		$this->view['title'] = lang('taxset');
		$this->view('tax/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->db->table('tax')->where("id=$id")->get();
				$this->view['data'] = $data;
			}
			$this->view['country_list'] = $this->db->table('country')->where('status=1')->getlist();
			$this->view('tax/edit.html');
		}else{
			if(!$_POST['name'] || !$_POST['country_id'] || !$_POST['defaut_tax']){
				$this->error('required_null');
			}
			
			$data = array(
				'name' => trim($_POST['name']),
				'country_id' => intval($_POST['country_id']),
				'defaut_tax' => floatval($_POST['defaut_tax']),
				'status' => intval($_POST['status']),
			);
			
			if($data['status'] == 1){
				$this->db->table('tax')->where("country_id=".$data['country_id'])->update(array('status' => 0));
			}
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->db->table('tax')->where("id=$id")->update($data);
			}else{
				$id = $this->db->table('tax')->insert($data);
			}
			
			$this->ok('edit_success', url('tax/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->db->table('tax_set')->where("tax_id=$id")->delete();
			$this->db->table('tax')->where("id=$id")->delete();
		}
		$this->ok('delete_done', url('tax/index'));
	}
	
	public function region()
	{
		$tax_id = intval($_GET['tax_id']);
		if(!$tax_id){
			$this->error('args_error');
		}
		
		$this->view['list'] = $this->db->table('tax_set')->where("tax_id=$tax_id")->getlist();
		$data = $this->db->table('tax')->where("id=$tax_id")->get();
		
		$country_id = $data['country_id'];
		$region = array();
		$region_list = $this->db->table('region')->where("country_id=$country_id")->getlist();
		foreach($region_list as $v){
			$region[$v['region_id']] = $v['name'];
		}
		
		$this->view['data'] = $data;
		$this->view['region'] = $region;
		$this->view['title'] = lang('tax_region');
		$this->view('tax/region.html');
	}
	
	public function editregion()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->db->table('tax_set')->where("id=$id")->get();
				$tax_id = $data['tax_id'];
				$this->view['data'] = $data;
			}else{
				$tax_id = intval($_GET['tax_id']);
			}
			
			if(!$tax_id){
				$this->error('args_error');
			}
			
			$country_id = $this->db->table('tax')->where("id=$tax_id")->getval('country_id');
			$this->view['region_list'] = $this->db->table('region')->where("country_id=$country_id")->getlist();
			$this->view['tax_id'] = $tax_id;
			$this->view('tax/editregion.html');
		}else{
			if(!$_POST['tax_id'] || !$_POST['region_id'] || !$_POST['tax']){
				$this->error('required_null');
			}
			
			$data = array(
				'tax_id' => intval($_POST['tax_id']),
				'region_id' => intval($_POST['region_id']),
				'tax' => floatval($_POST['tax']),
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->db->table('tax_set')->where("id=$id")->update($data);
			}else{
				$this->db->table('tax_set')->insert($data);
			}
			
			$this->ok('edit_success', url('tax/region?tax_id='.$data['tax_id']));
		}
	}
	
	public function delregion()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$tax_id = $this->db->table('tax_set')->where("id=$id")->getval('tax_id');
		$this->db->table('tax_set')->where("id=$id")->delete();
		
		$this->ok('delete_done', url('tax/region?tax_id='.$tax_id));
	}
}

?>