<?php
/*
*	@sitemap.php
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

class sitemap extends model
{
	public function changefreq()
	{
		return array(
			'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'
		);
	}
	
	public function getconfig()
	{
		$data = array();
		$list = $this->db->table('sitemap')->getlist();
		foreach($list as $v){
			$key = $v['name'];
			$data[$key] = $v['val'];
		}
		return $data;
	}
	
	public function generate()
	{
		$this->model('event')->add('backend', 'sitemap.generate');
	}
}
?>