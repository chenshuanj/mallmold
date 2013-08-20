<?php
/*
*	@goodscate.php
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

class goodscate extends model
{
	public function getlist($pid = 0)
	{
		$list = $this->db->table('goods_cate')->where("pid=$pid")->order('sort_order asc')->getlist();
		foreach($list as $key=>$val){
			$list[$key] = $this->model('dict')->getdict($val);
			$id = $val['id'];
			$rownum = $this->db->table('goods_cate')->where("pid=$id")->count();
			if($rownum > 0){
				$list[$key]['childnum'] = $rownum;
				$list[$key]['child'] = $this->getlist($id);
			}
		}
		return $list;
	}
}
?>