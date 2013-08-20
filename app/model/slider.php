<?php
/*
*	@slider.php
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

class slider extends model
{
	public function get($sign)
	{
		if(!$sign)
			return null;
		
		$list = $this->cache('slider_'.$sign);
		if(!$list){
			$slider = $this->db->table('slider')->where("sign='$sign'")->get();
			if(!$slider){
				return null;
			}
			$setting_id = $slider['setting_id'];
			$list = $this->model('mdata')->table('slider_image')->where('slider_id='.$slider['slider_id'])->getlist();
			foreach($list as $k=>$v){
				$list[$k]['src'] = $this->model('image')->getimgbyid($setting_id, $v['src']);
			}
			$this->cache('slider_'.$sign, $list);
		}
		return $list;
	}
}
?>