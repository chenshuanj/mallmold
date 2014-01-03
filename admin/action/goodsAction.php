<?php


require Action('common');

class goodsAction extends commonAction
{
	public function index()
	{
		if(isset($_POST['sku'])){
			$sku = trim($_POST['sku']);
			$_SESSION['goods_index_sku'] = $sku;
		}else{
			$sku = $_SESSION['goods_index_sku'];
		}
		
		$where = '';
		if($sku){
			$where = "sku like '%$sku%'";
		}
		
		$total = $this->db->table('goods')->where($where)->count();
		$this->pager($total);
		
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$group = array(0 => lang('default_group'));
		$group_list = $this->mdata('group')->getlist();
		foreach($group_list as $v){
			$group[$v['id']] = $v['name'];
		}
		
		$this->view['sku'] = $sku;
		$this->view['group'] = $group;
		$this->view['list'] = $this->mdata('goods')->where($where)->order('addtime desc')->limit($limit)->getlist();
		$this->view['sku'] = $sku;
		$this->view['title'] = lang('product_list');
		$this->view('goods/list.html');
	}
	
	public function add()
	{
		$goods_id = intval($_GET['goods_id']);
		if(!isset($_GET['group_id']) && !$goods_id){
			$group = array(0 => lang('default_group'));
			$group_list = $this->mdata('group')->where('status=1')->getlist();
			foreach($group_list as $v){
				$group[$v['id']] = $v['name'];
			}
			
			$this->view['group'] = $group;
			$this->view['title'] = lang('edit_product');
			$this->view('goods/select_group.html');
			return;
		}else{
			$group_id = intval($_GET['group_id']);
		}
		
		$data = $cates = $attr = $option = $extend = array();
		if($goods_id){
			$data = $this->mdata('goods')->where("goods_id=$goods_id")->get();
			$group_id = $data['group_id'];
			
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
			
			$optionlist = $this->mdata('goods_option')->where("goods_id=$goods_id")->order('sort_order asc')->getlist();
			if($optionlist){
				foreach($optionlist as $v){
					$op_id = $v['op_id'];
					$option[$op_id][] = $v;
				}
			}
			$data['option'] = $option;
			
			$cs_ids = $this->db->table('goods_crosssell')->where("goods_id=$goods_id")->getval('relate_ids');
			if($cs_ids){
				$this->view['cross_sell'] = $this->mdata('goods')->where("goods_id in ($cs_ids)")->getlist();
			}
			
		}else{
			$data['images'] = array();
			$data['group_id'] = $group_id;
		}
			
		$this->editor_header();
		$this->editor('description', $data['description'], 'description_txtkey_', $data['description_txtkey_'], 'goods_desc');
		$this->editor_uploadbutton('image', $data['image'], 'goods_main_img');
		$this->editor_multiuploadbutton('imagemore', $data['images'], 'goods_imgs');
		
		if($group_id == 0){
			$catelist = $this->model('goodscate')->getlist();
			$attrlist = $this->mdata('attribute')->where("status=1")->getlist();
			$extendlist = $this->model('extend')->getlist();
			$option = $this->mdata('option')->where("status=1")->getlist();
		}else{
			$catelist = $this->model('goodscate')->getgrouplist($group_id);
			$attrlist = $this->mdata('attribute')
							->where("status=1 and attr_id in (select attr_id from ".$this->db->tbname('group_attr')." where group_id=$group_id)")
							->getlist();
			$where = "extend_id in (select extend_id from ".$this->db->tbname('group_extend')." where group_id=$group_id)";
			$extendlist = $this->model('extend')->getlist($where);
			$option = $this->mdata('option')
							->where("status=1 and op_id in (select op_id from ".$this->db->tbname('group_option')." where group_id=$group_id)")
							->getlist();
		}
		
		foreach($attrlist as $k=>$v){
			if($attr[$v['attr_id']]){
				$attrlist[$k]['at'] = 1;
			}
			$attrlist[$k]['values'] = $this->mdata('attribute_value')->where("attr_id=".$v['attr_id'])->getlist();
		}
		
		$this->view['data'] = $data;
		$this->view['attrlist'] = $attrlist;
		$this->view['extendlist'] = $extendlist;
		$this->view['attr_at'] = $attr;
		$this->view['catelist'] = $catelist;
		$this->view['cate_at'] = $cates;
		$this->view['option'] = $option;
		$this->view['title'] = lang('edit_product');
		$this->view('goods/add.html');
	}
	
	public function update()
	{
		$group_id = intval($_POST['group_id']);
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
			'group_id' => $group_id,
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
			'meta_title_key_' => $_POST['meta_title_key_'],
			'meta_title' => trim($_POST['meta_title']),
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
		$op_name_id = $_POST['op_name_id'];
		$op_name = $_POST['op_name'];
		$op_name_key = $_POST['op_name_key'];
		$op_price = $_POST['op_price'];
		if($op_name && is_array($op_name) && is_array($op_price)){
			$option = array();
			$optionlist = $this->mdata('goods_option')->where("goods_id=$goods_id")->getlist();
			if($optionlist){
				foreach($optionlist as $v){
					$op_id = $v['op_id'];
					$oid = $v['id'];
					$option[$op_id][$oid] = $v;
				}
			}
			
			foreach($op_name as $op_id=>$v){
				$n = 1;
				foreach($v as $k=>$name){
					$oid = $op_name_id[$op_id][$k];
					$name = trim($name);
					if($name){
						$price = floatval($op_price[$op_id][$k]);
						$name_key = $op_name_key[$op_id][$k];
						if($option[$op_id][$oid]){
							$data = array(
								'name_key_' => $name_key,
								'name' => $name,
								'price' => $price,
								'sort_order' => $n
							);
							$this->mdata('goods_option')->where("id=$oid")->save($data);
							unset($option[$op_id][$oid]);
						}else{
							$data = array(
								'goods_id' => $goods_id,
								'op_id' => $op_id,
								'name_key_' => $name_key,
								'name' => $name,
								'image' => '',
								'price' => $price,
								'sort_order' => $n
							);
							$this->mdata('goods_option')->add($data);
						}
						$n++;
					}
				}
			}
			
			if($option){
				foreach($option as $op_id=>$val){
					if($val){
						foreach($val as $id=>$v){
							$this->mdata('goods_option')->where("id=$id")->delete();
						}
					}
				}
			}
		}
		
		//cross sell
		$cross_sell = $_POST['cross_sell'];
		if($cross_sell && is_array($cross_sell)){
			$cs_ids = implode(',', $cross_sell);
			$n = $this->db->table('goods_crosssell')->where("goods_id=$goods_id")->count();
			if($n > 0){
				$this->db->table('goods_crosssell')
						->where("goods_id=$goods_id")
						->update(array('relate_ids' => $cs_ids));
			}else{
				$data = array('goods_id'=>$goods_id, 'relate_ids'=>$cs_ids);
				$this->db->table('goods_crosssell')->insert($data);
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
	
	public function crosssell_search()
	{
		$sku = trim($_POST['keyword']);
		$ids = trim($_POST['ids']);
		$where = "goods_id not in ($ids)";
		if($sku){
			$where .= "sku like '%$sku%'";
		}
		
		$list = $this->mdata('goods')->where($where)->order('addtime desc')->limit(50)->getlist();
		$html = '';
		foreach($list as $v){
			$html .= '<tr id="cs_s_'.$v['goods_id'].'" class="cs_row">
		<td class="tleft" id="cs_s_sku_'.$v['goods_id'].'">'.$v['sku'].'</td>
		<td class="tleft" id="cs_s_title_'.$v['goods_id'].'">'.$v['title'].'</td>
		<td class="tleft"><a href="javascript:cs_add('.$v['goods_id'].')">'.lang('add').'</a></td>
	  </tr>';
		}
		echo $html;
	}
}

?>