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
	
	public function getgrouplist($group_id)
	{
		$bind_cate = array();
		$group_cate_list = $this->db->table('group_cate')->where("group_id=$group_id")->getlist();
		foreach($group_cate_list as $v){
			$bind_cate[] = $v['cate_id'];
		}
		
		if(!$bind_cate){
			return array();
		}
		
		$catelist = $this->model('goodscate')->getlist();
		foreach($catelist as $k=>$v){
			$v = $this->filterids($v, $bind_cate);
			if(!$v){
				unset($catelist[$k]);
			}else{
				$catelist[$k] = $v;
			}
		}
		return $catelist;
	}
	
	private function filterids($cate, $bind_cate)
	{
		$childids = $this->getchildids($cate);
		if(!array_intersect($childids, $bind_cate)){
			unset($cate['child']);
			unset($cate['childnum']);
		}
		if(!in_array($cate['id'], $bind_cate)){
			$cate['disable'] = 1;
			if(!$cate['child']){
				return null;
			}else{
				foreach($cate['child'] as $k=>$v){
					$v = $this->filterids($v, $bind_cate);
					if(!$v){
						unset($cate['child'][$k]);
					}else{
						$cate['child'][$k] = $v;
					}
				}
			}
		}
		return $cate;
	}
	
	private function getchildids($cate)
	{
		$ids = array();
		if($cate['child']){
			foreach($cate['child'] as $v){
				$ids[] = $v['id'];
				$ids = array_merge($ids, $this->getchildids($v));
			}
		}
		return $ids;
	}
}
?>