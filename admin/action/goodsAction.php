<?php
/*
*	@goodsAction.php
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

require Action('common');

class goodsAction extends commonAction
{
	public function index()
	{
		$where = '';
		$sku = trim($_POST['sku']);
		if($sku){
			$where = "sku like '%$sku%'";
		}
		
		$total = $this->db->table('goods')->where($where)->count();
		$this->pager($total);
		
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$this->view['list'] = $this->mdata('goods')->where($where)->limit($limit)->getlist();
		$this->view['sku'] = $sku;
		$this->view['title'] = lang('product_list');
		$this->view('goods/list.html');
	}
	
	public function add()
	{
		$goods_id = intval($_GET['goods_id']);
		$data = $cates = $attr = $option = $extend = array();
		if($goods_id){
			$data = $this->mdata('goods')->where("goods_id=$goods_id")->get();
			
			$attrlist = $this->db->table('goods_attr')->where("goods_id=$goods_id")->getlist();
			foreach($attrlist as $v){
				$attr[$v['attr_id']] = $v['av_id'];
			}
			
			$extendlist = $this->db->table('goods_extend')->where("goods_id=$goods_id")->getlist();
			foreach($extendlist as $v){
				$extend[$v['extend_id']] = $v['val'];
			}
			$data['extend'] = $extend;
			
			$data['images'] = $this->db->table('goods_image')->where("goods_id=$goods_id")->getlist();
			
			$catelist = $this->db->table('goods_cate_val')->where("goods_id=$goods_id")->getlist();
			if($catelist){
				foreach($catelist as $v){
					$cates[] = $v['cate_id'];
				}
			}
			
			$optionlist = $this->mdata('goods_option')->where("goods_id=$goods_id")->getlist();
			if($optionlist){
				foreach($optionlist as $v){
					$op_id = $v['op_id'];
					$option[$op_id][] = $v;
				}
			}
			$data['option'] = $option;
		}else{
			$data['images'] = array();
		}
			
		$this->editor_header();
		$this->editor('description', $data['description'], 'description_txtkey_', $data['description_txtkey_'], 'goods_desc');
		$this->editor_uploadbutton('image', $data['image'], 'goods_main_img');
		$this->editor_multiuploadbutton('imagemore', $data['images'], 'goods_imgs');
			
		$attrlist = $this->mdata('attribute')->where("status=1")->getlist();
		foreach($attrlist as $k=>$v){
			if($attr[$v['attr_id']]){
				$attrlist[$k]['at'] = 1;
			}
			$attrlist[$k]['values'] = $this->mdata('attribute_value')->where("attr_id=".$v['attr_id'])->getlist();
		}
		
		$this->view['data'] = $data;
		$this->view['attrlist'] = $attrlist;
		$this->view['extendlist'] = $this->model('extend')->getlist();
		$this->view['attr_at'] = $attr;
		$this->view['catelist'] = $this->model('goodscate')->getlist();
		$this->view['cate_at'] = $cates;
		$this->view['option'] = $this->mdata('option')->where("status=1")->getlist();
		$this->view['title'] = lang('edit_product');
		$this->view('goods/add.html');
	}
	
	public function update()
	{
		$title = trim($_POST['title']);
		$title_key_ = $_POST['title_key_'];
		$urlkey = to_url(trim($_POST['urlkey']));
		$sku = trim($_POST['sku']);
		$cate_id = $_POST['cate_id'];
		$price = floatval($_POST['price']);
		$stock = intval($_POST['stock']);
		$sort_order = intval($_POST['sort_order']);
		$goods_id = intval($_POST['goods_id']);
		
		if(!$title || !$sku){
			$this->error('required_null');
		}
		
		//check sku
		$row = $this->db->table('goods')->where("sku='$sku'")->get();
		if($row && $row['goods_id'] != $goods_id){
			$this->error('sku_repeated');
		}
		
		//if image is not uploaded
		$image = trim($_POST['image']);
		$image = $this->model('image')->check_img($image, 'goods_main_img');
		
		$data = array(
			'title' => $title,
			'title_key_' => $title_key_,
			'urlkey' => $urlkey,
			'sku' => $sku,
			'price' => $price,
			'price_origin' => floatval($_POST['price_origin']),
			'weight' => intval($_POST['weight']),
			'brief_txtkey_' => $_POST['brief_txtkey_'],
			'brief' => trim($_POST['brief']),
			'description_txtkey_' => $_POST['description_txtkey_'],
			'description' => $_POST['description'],
			'meta_keywords_txtkey_' => $_POST['meta_keywords_txtkey_'],
			'meta_keywords' => trim($_POST['meta_keywords']),
			'meta_description_txtkey_' => $_POST['meta_description_txtkey_'],
			'meta_description' => trim($_POST['meta_description']),
			'image' => $image,
			'stock' => $stock,
			'is_sale' => intval($_POST['is_sale']),
			'sort_order' => $sort_order,
		);
		
		if($goods_id>0){
			$this->mdata('goods')->where("goods_id=$goods_id")->save($data);
		}else{
			$data['addtime'] = time();
			$goods_id = $this->mdata('goods')->add($data);
			if($goods_id < 1){
				$this->error('add_error');
			}
		}
		
		$this->model('urlkey')->set_goods($goods_id, $data['urlkey']);
		
		//category
		if($cate_id && is_array($cate_id)){
			$cates = array();
			$catelist = $this->db->table('goods_cate_val')->where("goods_id=$goods_id")->getlist();
			if($catelist){
				foreach($catelist as $v){
					$cates[$v['cate_id']] = $v['cate_id'];
				}
			}
			
			foreach($cate_id as $id){
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
		}
		
		//attribute
		$attr = $_POST['attr'];
		$attr_value = $_POST['attr_value'];
		if($attr && is_array($attr) && $attr_value && is_array($attr_value)){
			$attrlist = array();
			$list = $this->db->table('goods_attr')->where("goods_id=$goods_id")->getlist();
			foreach($list as $v){
				$attrlist[$v['attr_id']] = $v['av_id'];
			}
			
			foreach($attr as $aid){
				if(!$attrlist[$aid] && $attr_value[$aid]){
					$this->db->table('goods_attr')->insert(array('goods_id'=>$goods_id, 'attr_id'=>$aid, 'av_id'=>$attr_value[$aid]));
				}else{
					if($attrlist[$aid] != $attr_value[$aid]){
						$this->db->table('goods_attr')->where("goods_id=$goods_id and attr_id=$aid")->update(array('av_id'=>$attr_value[$aid]));
					}
					unset($attrlist[$aid]);
				}
			}
			
			if($attrlist){
				foreach($attrlist as $k=>$v){
					$this->db->table('goods_attr')->where("goods_id=$goods_id and attr_id=$k")->delete();
				}
			}
		}
		
		//extend
		$extend = $_POST['extend'];
		if($extend && is_array($extend)){
			$extend_at = array();
			$extendlist = $this->db->table('goods_extend')->where("goods_id=$goods_id")->getlist();
			foreach($extendlist as $v){
				$extend_at[$v['extend_id']] = $v['val'];
			}
			
			foreach($extend as $extend_id=>$v){
				if(is_array($v)){
					$v = implode(',', $v);
				}
				$v = trim($v);
				if(!empty($v)){
					if(!$extend_at[$extend_id]){
						$this->db->table('goods_extend')->insert(array('goods_id'=>$goods_id, 'extend_id'=>$extend_id, 'val'=>$v));
					}elseif($v != $extend_at[$extend_id]){
						$this->db->table('goods_extend')->where("goods_id=$goods_id and extend_id=$extend_id")->update(array('val'=>$v));
					}
				}else{
					$this->db->table('goods_extend')->where("goods_id=$goods_id and extend_id=$extend_id")->delete();
				}
			}
		}
		
		//image
		$imagemore = $_POST['imagemore'];
		if($imagemore && is_array($imagemore)){
			$imglist = array();
			$list = $this->db->table('goods_image')->where("goods_id=$goods_id")->getlist();
			foreach($list as $v){
				$imglist[] = $v['image'];
			}
			
			$s_arr = array();
			foreach($imagemore as $img){
				if(!in_array($img, $imglist)){
					$this->db->table('goods_image')->insert(array('goods_id'=>$goods_id, 'image'=>$img));
				}else{
					$s_arr[] = $img;
				}
			}
			$diff_arr = array_diff($imglist, $s_arr);
			if($diff_arr){
				foreach($diff_arr as $v){
					$this->db->table('goods_image')->where("goods_id=$goods_id and image='$v'")->delete();
				}
			}
		}
		
		//option
		$op_name = $_POST['op_name'];
		$op_name_key = $_POST['op_name_key'];
		$op_price = $_POST['op_price'];
		if($op_name && is_array($op_name) && is_array($op_price)){
			$option = array();
			$optionlist = $this->mdata('goods_option')->where("goods_id=$goods_id")->getlist();
			if($optionlist){
				foreach($optionlist as $v){
					$op_id = $v['op_id'];
					$name = $v['name'];
					$option[$op_id][$name] = $v;
				}
			}
			
			foreach($op_name as $op_id=>$v){
				foreach($v as $k=>$name){
					$name = trim($name);
					if($name){
						$price = floatval($op_price[$op_id][$k]);
						$name_key = $op_name_key[$op_id][$k];
						if(!$option[$op_id][$name]){
							$data = array(
								'goods_id' => $goods_id,
								'op_id' => $op_id,
								'name_key_' => $name_key,
								'name' => $name,
								'image' => '',
								'price' => $price,
								'sort_order' => $k
							);
							$this->mdata('goods_option')->add($data);
						}elseif($price != floatval($option[$op_id][$name]['price']) && $name_key == $option[$op_id][$name]['name_key_']){
							$data = array(
								'price' => $price,
								'sort_order' => $k
							);
							$where = "goods_id=$goods_id and op_id=$op_id and name_key_='$name_key'";
							$this->mdata('goods_option')->where($where)->save($data);
						}else{
							unset($option[$op_id][$name]);
						}
					}
				}
			}
			
			if($option){
				foreach($option as $op_id=>$val){
					if($val){
						foreach($val as $v){
							$where = "goods_id=$goods_id and op_id=$op_id and name_key_='".$v['name_key_']."'";
							$this->mdata('goods_option')->where($where)->delete();
						}
					}
				}
			}
		}
		
		$this->ok('edit_success', url('goods/index'));
	}
	
	public function del()
	{
		$goods_id = intval($_GET['goods_id']);
		if(!$goods_id){
			$this->error('args_error');
		}
		
		$this->db->table('goods_cate_val')->where("goods_id=$goods_id")->delete();
		$this->db->table('goods_image')->where("goods_id=$goods_id")->delete();
		$this->db->table('goods_attr')->where("goods_id=$goods_id")->delete();
		$this->mdata('goods_option')->where("goods_id=$goods_id")->delete();
		$this->mdata('goods')->where("goods_id=$goods_id")->delete();
		$this->model('urlkey')->del_goods($goods_id);
		
		$this->ok('delete_done', url('goods/index'));
	}
}

?>