<?php
/*
*	@region.php
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

class region extends model
{
	public function country_list()
	{
		$list = $this->cache('country_list');
		if(!$list){
			$list = $this->db->table('country')->where("status=1")->getlist();
			$this->cache('country_list', $list);
		}
		return $list;
	}
	
	public function region_list($country_id)
	{
		$key = 'region_list_'.$country_id;
		$list = $this->cache($key);
		if(!$list){
			$list = $this->db->table('region')->where("country_id=$country_id")->order('sort_order asc')->getlist();
			$this->cache($key, $list);
		}
		return $list;
	}
	
	public function city_list($region_id)
	{
		$key = 'city_list_'.$region_id;
		$list = $this->cache($key);
		if(!$list){
			//country code
			$country_id  = $this->db->table('region')->where("region_id=$region_id")->getval('country_id');
			$code = $this->db->table('country')->where("id=$country_id")->getval('code');
			$table = 'region_city_'.strtolower($code);
			
			$list = $this->db->table($table)->where("region_id=$region_id")->order('sort_order asc')->getlist();
			$this->cache($key, $list);
		}
		return $list;
	}
	
	public function get_country_name($id)
	{
		return $this->db->table('country')->where("id=$id")->getval('name');
	}
	
	public function get_region_name($region_id)
	{
		return $this->db->table('region')->where("region_id=$region_id")->getval('name');
	}
	
	public function ajax_options($country_id)
	{
		$options = '';
		if($country_id){
			$region_list = $this->region_list($country_id);
			foreach($region_list as $v){
				$options .= '<option value="'.$v['region_id'].'">'.$v['name'].'</option>';
			}
		}else{
			$options = '<option value="0">-'.lang('Select country').'-</option>';
		}
		return $options;
	}
}
?>