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
	public $list_field = 'goods_id,title_key_,urlkey,sku,price_origin,price,brief_txtkey_,image,addtime';
	
	public function base_condition()
	{
		$setting = &$this->model('common')->setting();
		$condition = "status=1";
		if($setting['show_unsale'] == 0){
			$condition .= " and is_sale=1";
		}
		return $condition;
	}
	
	public function get_count($where='')
	{
		$condition = $this->base_condition();
		$where && $condition .= " and $where";
		return $this->db->table('goods')->where($condition)->count();
	}
	
	public function getlist($where='', $order='', $limit='') //no cahce
	{
		$setting = &$this->model('common')->setting();
		$condition = $this->base_condition();
		$where && $condition .= " and $where";
		
		if(!$order){
			$order = $setting['goods_order'].' desc';
		}
		
		$list = $this->model('mdata')->table('goods')
									->field($this->list_field)
									->where($condition)
									->order($order)
									->limit($limit)
									->getlist();
		foreach($list as $k=>$v){
			$list[$k]['image'] = $this->model('image')->getimgbytype('goods_main_img', $v['image']);
			$list[$k]['price'] = $this->model('common')->current_price($v['price']);
			$list[$k]['price_origin'] = $this->model('common')->current_price($v['price_origin']);
			$list[$k]['url'] = $this->model('urlkey')->geturl('goods_id', $v['goods_id'], $v['urlkey']);
			$list[$k]['score'] = $this->model('comment')->get_score($v['goods_id']);
			//$list[$k]['extend'] = $this->get_goods_extend($v['goods_id']);
		}
		
		return $list;
	}
	
	public function get($goods_id)
	{
		if(!$goods_id)
			return null;
		
		$data = $this->model('mdata')->table('goods')->where("goods_id=$goods_id")->get();
		$data['price_origin'] = $this->model('common')->current_price($data['price_origin'], 0);
		//$data['url'] = $this->model('urlkey')->geturl('goods_id', $data['goods_id'], $data['urlkey']);
		$data['price'] = $this->model('common')->current_price($data['price'], 0);
		$data['image'] = $this->model('image')->getimgbytype('goods_main_img', $data['image']);
		$data['img_more'] = $this->goods_img_more($data['goods_id']);
		$data['extend'] = $this->get_goods_extend($data['goods_id']);
		$data['attr'] = $this->get_goods_attr($data['goods_id']);
		$data['option'] = $this->get_price_option($data['goods_id']);
		$data['score'] = $this->model('comment')->get_score($goods_id);
		return $data;
	}
	
	public function search_list($keys)
	{
		$match_list = array();
		
		if(!$keys){
			return $match_list;
		}
		
		$sql = 'select goods_id,title_key_ from `'.$this->db->tbname('goods').'` 
				where '.$this->base_condition().' 
				order by sort_order asc';
		$query = $this->db->query($sql);
		while($rs = $this->db->fetch($query)){
			$rs = $this->model('dictionary')->getdict($rs);
			foreach($keys as $key){
				if(stripos($rs['title'], $key) !== false){
					$match_list[] = $rs['goods_id'];
					break;
				}
			}
		}
		return $match_list;
	}
	
	public function goods_img_more($goods_id)
	{
		$list = $this->model('mdata')->table('goods_image')->where("goods_id=$goods_id")->getlist();
		foreach($list as $k=>$v){
			$list[$k]['image'] = $this->model('image')->getimgbytype('goods_imgs', $v['image']);
		}
		return $list;
	}
	
	public function get_goods_attr($goods_id)
	{
		$attr = array();
		$goods_attr = $this->db->table('goods_attr')->where("goods_id=$goods_id")->getlist();
		if($goods_attr){
			$attribute_list = $this->model('catalog')->get_attributes();
			$n = 1;
			foreach($goods_attr as $v){
				$attr_id = $v['attr_id'];
				$av_id = $v['av_id'];
				$attribute = $attribute_list[$attr_id];
				$code = $attribute['code'] ? $attribute['code'] : $n++;
				$attr[$code] = array(
						'name' => $attribute['name'],
						'value' => $attribute['values'][$av_id]['title'],
					);
			}
		}
		return $attr;
	}
	
	public function get_goods_extend($goods_id)
	{
		$extends = array();
		$goods_extend = $this->db->table('goods_extend')->where("goods_id=$goods_id")->getlist();
		if($goods_extend){
			$extend_list = $this->model('catalog')->get_extends();
			$n = 1;
			foreach($goods_extend as $v){
				$extend_id = $v['extend_id'];
				switch($extend_list[$extend_id]['type']){
					case 1:
						$value = $v['val'];
						break;
					case 2:
						$value = $extend_list[$extend_id]['values'][$v['val']]['val'];
						break;
					case 3:
						$value = array();
						$values = explode(',', $v['val']);
						foreach($values as $val){
							$value[] = $extend_list[$extend_id]['values'][$val]['val'];
						}
						$value = implode(",\n", $value); //string
						break;
					case 4:
						$value = $v['val'] ? lang('Yes') : lang('No');
						break;
					case 5:
						if($v['val']){
							$value = '<a href="'.$v['val'].'">'.basename($v['val']).'</a>';
						}else{
							$value = '';
						}
						break;
					default:
						$value = $v['val'];
				}
				
				$code = $extend_list[$extend_id]['code'] ? $extend_list[$extend_id]['code'] : $n++;
				$extends[$code] = array(
						'name' => $extend_list[$extend_id]['name'],
						'value' => $value,
					);
			}
		}
		return $extends;
	}
	
	public function get_price_option($goods_id)
	{
		$options = array();
		$goods_option = $this->model('mdata')->table('goods_option')->where("goods_id=$goods_id")->order('sort_order asc')->getlist();
		if($goods_option){
			$options_list = $this->model('catalog')->get_options();
			foreach($goods_option as $k=>$v){
				$op_id = $v['op_id'];
				$id = $v['id'];
				if(!$options[$op_id]){
					$options[$op_id] = $options_list[$op_id];
				}
				$options[$op_id]['option'][$id] = $v;
			}
		}
		return $options;
	}
	
	public function get_cross_sell($good_ids)
	{
		if(!$good_ids){
			return null;
		}
		
		$list = $this->db->table('goods_crosssell')->where("goods_id in ($good_ids)")->getlist();
		$relate_ids = '';
		foreach($list as $v){
			$relate_ids .= ($relate_ids ? ',' : '').$v['relate_ids'];
		}
		
		if($relate_ids){
			return $this->getlist("goods_id in ($relate_ids)");
		}else{
			return array();
		}
	}
	
	public function save_keywords(array $keywords)
	{
		if(!$keywords){
			return null;
		}
		foreach($keywords as $v){
			$v = trim($v);
			$row = $this->db->table('keywords')->where("keyword='$v'")->get();
			if($row){
				$search_num = $row['search_num'] + 1;
				$this->db->table('keywords')->where("id=".$row['id'])->update(array('search_num'=>$search_num));
			}else{
				$this->db->table('keywords')->insert(array('keyword'=>$v));
			}
		}
		return true;
	}
}
?>