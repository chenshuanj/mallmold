<?php
/*
*	@areaAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require Action('common');

class areaAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->db->table('country')->getlist();
		$this->view['title'] = lang('country_list');
		$this->view('area/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->db->table('country')->where("id=$id")->get();
				$this->view['data'] = $data;
			}
			$this->view('area/edit.html');
		}else{
			if(!$_POST['name'] || !$_POST['code']){
				$this->error('required_null');
			}
			
			$code = trim($_POST['code']);
			if(!preg_match('/^[A-Z]{2}$/', $code)){
				$this->error('countrycode_error');
			}
			
			$data = array(
				'code' => $code,
				'name' => trim($_POST['name']),
				'phone_code' => trim($_POST['phone_code']),
				'time_zone' => floatval($_POST['time_zone']),
				'status' => intval($_POST['status'])
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$code = $this->db->table('country')->where("id=$id")->getval('code');
				if($code != $data['code']){
					$this->db->query("RENAME TABLE `mm_region_city_".$code."` TO `mm_region_city_".$data['code']."`");
				}
				$this->db->table('country')->where("id=$id")->update($data);
			}else{
				$id = $this->db->table('country')->insert($data);
				$this->db->query("CREATE TABLE mm_region_city_".$data['code']." LIKE `mm_region_city`");
			}
			
			$this->ok('edit_success', url('area/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$code = $this->db->table('country')->where("id=$id")->getval('code');
			$this->db->table('region')->where("country_id=$id")->delete();
			$this->db->table('country')->where("id=$id")->delete();
			$this->db->query("DROP TABLE `mm_region_city_".$code."`");
		}
		$this->ok('delete_done', url('area/index'));
	}
	
	public function region()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$data = $this->db->table('country')->where("id=$id")->get();
		$this->view['data'] = $data;
		
		$total = $this->db->table('region')->where("country_id=$id")->count();
		$this->pager($total);
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$this->view['list'] = $this->db->table('region')->where("country_id=$id")->limit($limit)->getlist();
		$this->view['title'] = $data['name'].' > '.lang('region_list');
		$this->view['country_id'] = $id;
		$this->view('area/region.html');
	}
	
	public function editregion()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['region_id']);
			if($id){
				$data = $this->db->table('region')->where("region_id=$id")->get();
				$country_id = $data['country_id'];
				$this->view['data'] = $data;
			}else{
				$country_id = intval($_GET['country_id']);
				if(!$country_id){
					$this->error('args_error');
				}
			}
			$this->view['country_id'] = $country_id;
			$this->view('area/editregion.html');
		}else{
			if(!$_POST['name']){
				$this->error('required_null');
			}
			if(!$_POST['country_id']){
				$this->error('unselect_country');
			}
			
			$data = array(
				'country_id' => intval($_POST['country_id']),
				'name' => trim($_POST['name']),
				'code' => trim($_POST['code']),
				'sort_order' => intval($_POST['sort_order'])
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->db->table('region')->where("region_id=$id")->update($data);
			}else{
				$id = $this->db->table('region')->insert($data);
			}
			
			$this->ok('edit_success', url('area/region?id='.$data['country_id']));
		}
	}
	
	public function delregion()
	{
		$id = intval($_GET['region_id']);
		if($id>0){
			$country_id = $this->db->table('region')->where("region_id=$id")->getval('country_id');
			$this->db->table('region')->where("region_id=$id")->delete();
			$this->ok('delete_done', url('area/region?id='.$country_id));
		}else{
			$this->error('args_error');
		}
	}
	
	public function city()
	{
		$region_id = intval($_GET['region_id']);
		$table = $this->getcitytable($region_id);
		
		$total = $this->db->table($table)->where("region_id=$region_id")->count();
		$this->pager($total);
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$this->view['list'] = $this->db->table($table)->where("region_id=$region_id")->limit($limit)->getlist();
		$this->view['title'] = lang('city_list');
		$this->view['region_id'] = $region_id;
		$this->view['region'] = $this->db->table('region')->where("region_id=$region_id")->get();
		$this->view('area/city.html');
	}
	
	public function editcity()
	{
		$region_id = intval($_GET['region_id']);
		$table = $this->getcitytable($region_id);
		
		if(!$_POST['submit']){
			$city_id = intval($_GET['city_id']);
			if($city_id){
				$data = $this->db->table($table)->where("city_id=$city_id")->get();
				$this->view['data'] = $data;
			}
			$this->view['region_id'] = $region_id;
			$this->view('area/editcity.html');
		}else{
			if(!$_POST['name']){
				$this->error('required_null');
			}
			
			$data = array(
				'region_id' => $region_id,
				'name' => trim($_POST['name']),
				'postcode' => trim($_POST['postcode']),
				'sort_order' => intval($_POST['sort_order'])
			);
			
			if($_POST['city_id']){
				$city_id = intval($_POST['city_id']);
				$this->db->table($table)->where("city_id=$city_id")->update($data);
			}else{
				$this->db->table($table)->insert($data);
			}
			
			$this->ok('edit_success', url('area/city?region_id='.$region_id));
		}
	}
	
	public function delcity()
	{
		$region_id = intval($_GET['region_id']);
		$table = $this->getcitytable($region_id);
		
		$city_id = intval($_GET['city_id']);
		if($city_id>0){
			$this->db->table($table)->where("city_id=$city_id")->delete();
			$this->ok('delete_done', url('area/city?region_id='.$region_id));
		}else{
			$this->error('args_error');
		}
	}
	
	private function getcitytable($region_id)
	{
		if(!$region_id){
			$this->error('args_error');
		}
		$country_id = $this->db->table('region')->where("region_id=$region_id")->getval('country_id');
		$code = $this->db->table('country')->where("id=$country_id")->getval('code');
		return 'region_city_'.strtolower($code);
	}
}

?>