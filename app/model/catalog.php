<?php
/*
*	@catalog.php
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

class catalog extends model
{
	public $list_field = 'id,pid,name_key_,urlkey,image';
	
	public function get($id)
	{
		$cate = $this->cache('goods_cate_'.$id);
		if(!$cate){
			$cate = $this->model('mdata')->table('goods_cate')->where("id=$id")->get();
			$this->cache('goods_cate_'.$id, $cate);
		}
		return $cate;
	}
	
	public function get_catelist($pid = 0)
	{
		$list = $this->model('mdata')->table('goods_cate')
									->field($this->list_field)
									->where("pid=$pid")
									->order('sort_order asc')
									->getlist();
		foreach($list as $key=>$val){
			$id = $val['id'];
			$list[$key]['url'] = $this->model('urlkey')->geturl('id', $id, $val['urlkey']);
			//$list[$key]['image'] = $this->model('image')->getimgbytype('goods_cate', $val['image']);
			$rownum = $this->db->table('goods_cate')->where("pid=$id")->count();
			if($rownum > 0){
				$list[$key]['childnum'] = $rownum;
				$list[$key]['child'] = $this->get_catelist($id);
			}
		}
		return $list;
	}
	
	public function get_attributes()
	{
		$attributes = $this->cache('attributes');
		if(!$attributes){
			$attributes = array();
			$attribute_list = $this->model('mdata')->table('attribute')->where("status=1")->getlist();
			foreach($attribute_list as $k=>$v){
				$attr_id = $v['attr_id'];
				$values = array();
				$value_list = $this->model('mdata')->table('attribute_value')->where("attr_id=$attr_id")->getlist();
				foreach($value_list as $val){
					$av_id = $val['av_id'];
					$values[$av_id] = $val;
				}
				$v['values'] = $values;
				$attributes[$attr_id] = $v;
				
			}
			$this->cache('attributes', $attributes);
		}
		return $attributes;
	}
	
	public function get_extends()
	{
		$extendlist = $this->cache('extendlist');
		if(!$extendlist){
			$extendlist = array();
			$extend_list = $this->model('mdata')->table('extend')->where("status=1")->getlist();
			foreach($extend_list as $v){
				if($v['type']==2 || $v['type']==3){
					$values = array();
					$list = $this->model('mdata')
										->table('extend_val')
										->where("extend_id=".$v['extend_id'])
										->order('sort_order asc')
										->getlist();
					foreach($list as $val){
						$values[$val['id']] = $val;
					}
					$v['values'] = $values;
				}
				$extendlist[$v['extend_id']] = $v;
			}
			$this->cache('extendlist', $extendlist);
		}
		return $extendlist;
	}
	
	public function get_options()
	{
		$optionlist = $this->cache('optionlist');
		if(!$optionlist){
			$optionlist = array();
			$option_list = $this->model('mdata')->table('option')->where("status=1")->getlist();
			foreach($option_list as $v){
				$optionlist[$v['op_id']] = $v;
			}
			$this->cache('optionlist', $optionlist);
		}
		return $optionlist;
	}
	
	public function cate_map($id)
	{
		$map = array();
		$cate = $this->get($id);
		if($cate){
			$map[] = array('title' => $cate['name']);
		}else{
			return $map;
		}
		
		$n = 0;
		while($n < 2){
			if($cate['pid'] > 0){
				$cate = $this->get($cate['pid']);
				$map[] = array(
					'title' => $cate['name'],
					'url' => $this->model('urlkey')->geturl('id', $cate['id'], $cate['urlkey']),
				);
				$n++;
			}else{
				break;
			}
		}
		
		return array_reverse($map);
	}
	
	public function goods_map($goods_id)
	{
		$map = array();
		$list = $this->db->table('goods_cate_val')->where("goods_id=$goods_id")->getlist();
		if($list){
			$catelist = $this->get_catelist();
			$n = 0;
			while($n < 3){
				$cate = $this->find_goods_map($catelist, $list);
				if($cate){
					$map[] = array(
						'title' => $cate['name'],
						'url' => $cate['url'],
					);
					
					$catelist = $cate['child'];
					$n++;
				}else{
					break;
				}
			}
		}
		return $map;
	}
	
	private function find_goods_map($catelist, $vallist)
	{
		if(!$catelist || !$vallist || !is_array($catelist) || !is_array($vallist)){
			return null;
		}
		
		$cates = array();
		foreach($catelist as $v){
			$id = $v['id'];
			$cates[$id] = $v;
		}
		
		foreach($vallist as $v){
			$cate_id = $v['cate_id'];
			if($cates[$cate_id]){
				return $cates[$cate_id];
			}
		}
		
		return null;
	}
	
}
?>