<?php
/*
*	@customer.php
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

class customer extends model
{
	public function groups()
	{
		$group = array(0 => lang('none'));
		$group_list = $this->model('mdata')->table('user_group')->getlist();
		foreach($group_list as $v){
			$group[$v['group_id']] = $v['name'];
		}
		return $group;
	}
	
	public function languages()
	{
		$languages = array();
		$list = $this->db->table('language')->getlist();
		foreach($list as $v){
			$languages[$v['code']] = $v['code'];
		}
		return $languages;
	}
	
	public function countries()
	{
		$countries = array(0 => lang('none'));
		$country_list = $this->db->table('country')->field('id,name')->getlist();
		foreach($country_list as $v){
			$countries[$v['id']] = $v['name'];
		}
		return $countries;
	}
	
	public function regions()
	{
		$regions = array(0 => lang('none'));
		$region_list = $this->db->table('region')->field('region_id,name')->getlist();
		foreach($region_list as $v){
			$regions[$v['region_id']] = $v['name'];
		}
		return $regions;
	}
}
?>