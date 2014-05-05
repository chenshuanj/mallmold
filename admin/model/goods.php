<?php
/*
*	@goods.php
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

class goods extends model
{
	public function groups($status = 1)
	{
		$where = ($status == 1 ? 'status=1' : '');
		
		$group = array(0 => lang('default_group'));
		$group_list = $this->model('mdata')->table('group')->where($where)->getlist();
		foreach($group_list as $v){
			$group[$v['id']] = $v['name'];
		}
		return $group;
	}
	
	public function options()
	{
		$option = array();
		$list = $this->model('mdata')->table('option')->where("status=1")->getlist();
		foreach($list as $v){
			$option[$v['op_id']] = $v['name'];
		}
		return $option;
	}
	
	public function extensions()
	{
		$extension = array();
		$list = $this->model('mdata')->table('extend')->getlist();
		foreach($list as $extend){
			if($extend['type'] == 2 || $extend['type'] == 3){
				$values = array();
				$value_list = $this->model('mdata')->table('extend_val')->where("extend_id=".$extend['extend_id'])->getlist();
				foreach($value_list as $value){
					$values[$value['id']] = $value['val'];
				}
				$extend['values'] = $values;
			}
			$extension[$extend['extend_id']] = $extend;
		}
		return $extension;
	}
	
	public function get_extend($extend, $goods_id)
	{
		$extend_id = $extend['extend_id'];
		$val = $this->db->table('goods_extend')->where("goods_id=$goods_id and extend_id=$extend_id")->getval('val');
		if($val){
			if($extend['type'] == 2){
				$id = intval($val);
				$val = $extend['values'][$id];
			}elseif($extend['type'] == 3){
				$ids = explode(',', $val);
				$val =array();
				foreach($ids as $id){
					$id = intval($val);
					$val[] = $extend['values'][$id];
				}
			}
		}
		return $val;
	}
	
	public function attributes()
	{
		$attributes = array();
		$list = $this->model('mdata')->table('attribute')->getlist();
		foreach($list as $attribute){
			$values = array();
			$value_list = $this->model('mdata')->table('attribute_value')->where("attr_id=".$attribute['attr_id'])->getlist();
			foreach($value_list as $value){
				$values[$value['av_id']] = $value['title'];
			}
			$attribute['values'] = $values;
			$attributes[$attribute['attr_id']] = $attribute;
		}
		return $attributes;
	}
	
	public function get_attribute($attribute, $goods_id)
	{
		$value = null;
		$attr_id = $attribute['attr_id'];
		if($attribute['type'] == 1){
			$av_id = $this->db->table('goods_attr')->where("goods_id=$goods_id and attr_id=$attr_id")->getval('av_id');
			if($av_id){
				$value = $attribute['values'][$av_id];
			}
		}else{
			$list = $this->db->table('goods_attr')->where("goods_id=$goods_id and attr_id=$attr_id")->getlist();
			if($list){
				$value = array();
				foreach($list as $attr){
					$av_id = $attr['av_id'];
					$value[] = $attribute['values'][$av_id];
				}
			}
		}
		return $value;
	}
	
	public function update_cate($goods_id, array $cate_ids)
	{
		$cates = array();
		$catelist = $this->db->table('goods_cate_val')->where("goods_id=$goods_id")->getlist();
		if($catelist){
			foreach($catelist as $v){
				$cates[$v['cate_id']] = $v['cate_id'];
			}
		}
			
		foreach($cate_ids as $id){
			if(!in_array($id, $cates)){
				$this->db->table('goods_cate_val')->insert(array('goods_id'=>$goods_id, 'cate_id'=>$id));
			}else{
				unset($cates[$id]);
			}
		}
			
		if($cates){
			foreach($cates as $v){
				$this->db->table('goods_cate_val')->where("goods_id=$goods_id and cate_id=$v")->delete();
			}
		}
		
		return null;
	}
	
	public function getcrosssell($goods_id)
	{
		$skus = array();
		$ids = $this->db->table('goods_crosssell')->where("goods_id=$goods_id")->getval('relate_ids');
		if($ids){
			$list = $this->db->table('goods')->field('sku')->where("goods_id in ($ids)")->getlist();
			foreach($list as $row){
				$skus[] = $row['sku'];
			}
		}
		return $skus;
	}
	
	public function setcrosssell($goods_id, array $skus)
	{
		$ids = array();
		foreach($skus as $sku){
			$id = $this->db->table('goods')->where("sku='$sku'")->getval('goods_id');
			if($id){
				$ids[$id] = $id;
			}
		}
		
		if(!$ids){
			return false;
		}
		
		$cs_ids = implode(',', $ids);
		$n = $this->db->table('goods_crosssell')->where("goods_id=$goods_id")->count();
		if($n > 0){
			$this->db->table('goods_crosssell')
						->where("goods_id=$goods_id")
						->update(array('relate_ids' => $cs_ids));
		}else{
			$data = array('goods_id'=>$goods_id, 'relate_ids'=>$cs_ids);
			$this->db->table('goods_crosssell')->insert($data);
		}
		
		return true;
	}
}
?>