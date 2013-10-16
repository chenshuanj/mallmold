<?php
/*
*	@address.php
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

class address extends model
{
	public function address_list()
	{
		$user_id = $_SESSION['user_id'];
		$list = $this->db->table('user_address')->where("user_id=$user_id")->getlist();
		foreach($list as $k=>$v){
			$list[$k]['country'] = $this->model('region')->get_country_name($v['country_id']);
			$list[$k]['state'] = $this->model('region')->get_region_name($v['region_id']);
		}
		return $list;
	}
	
	public function get_address($id)
	{
		return $this->db->table('user_address')->where("id=$id")->get();
	}
	
	public function save_address(array $data, $id=0)
	{
		$address = array(
			'user_id' => $_SESSION['user_id'],
			'firstname' => trim($data['firstname']),
			'lastname' => trim($data['lastname']),
			'country_id' => intval($data['country_id']),
			'region_id' => intval($data['region_id']),
			'city' => trim($data['city']),
			'address' => trim($data['address']),
			'address2' => trim($data['address2']),
			'phone' => trim($data['phone']),
			'postcode' => trim($data['postcode']),
			
			'bill_firstname' => trim($data['bill_firstname']),
			'bill_lastname' => trim($data['bill_lastname']),
			'bill_country_id' => intval($data['bill_country_id']),
			'bill_region_id' => intval($data['bill_region_id']),
			'bill_city' => trim($data['bill_city']),
			'bill_address' => trim($data['bill_address']),
			'bill_address2' => trim($data['bill_address2']),
			'bill_phone' => trim($data['bill_phone']),
			'bill_postcode' => trim($data['bill_postcode']),
		);
		foreach($address as $k=>$v){
			if(!in_array($k, array('lastname','address2', 'bill_lastname', 'bill_address2')) && !$v){
				return false;
			}
		}
		
		if($id > 0){
			$this->db->table('user_address')->where("id=$id")->update($address);
			return $id;
		}else{
			return $this->db->table('user_address')->insert($address);
		}
	}
	
	public function del_address($id)
	{
		return $this->db->table('user_address')->where("id=$id")->delete();
	}
}
?>