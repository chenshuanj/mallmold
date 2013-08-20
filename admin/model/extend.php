<?php
/*
*	@extend.php
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

class extend extends model
{
	public function type()
	{
		return array(
			1 => lang('string'),
			2 => lang('selection'),
			3 => lang('multiple_selection'),
			4 => lang('bool_type'),
		);
	}
	
	public function getlist()
	{
		$list = $this->model('mdata')->table('extend')->where("status=1")->getlist();
		foreach($list as $k=>$v){
			if($v['type']==2 || $v['type']==3){
				$list[$k]['values'] = $this->model('mdata')
											->table('extend_val')
											->where("extend_id=".$v['extend_id'])
											->order('sort_order asc')
											->getlist();
			}
		}
		return $list;
	}
}
?>