<?php
/*
*	@discountAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require Action('common');

class discountAction extends commonAction
{
	public function index()
	{
		$list = $this->mdata('discount')->getlist();
		foreach($list as $k=>$v){
			$list[$k]['starttime'] = date('Y-m-d H:i:s', $v['starttime']);
			$list[$k]['endtime'] = date('Y-m-d H:i:s', $v['endtime']);
		}
		
		$this->view['list'] = $list;
		$this->view['title'] = lang('discount');
		$this->view('discount/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->mdata('discount')->where("id=$id")->get();
				$data['starttime'] = date('Y-m-d H:i:s', $data['starttime']);
				$data['endtime'] = date('Y-m-d H:i:s', $data['endtime']);
				$this->view['data'] = $data;
			}
			$this->view['type'] = $this->model('discount')->type();
			$this->view('discount/edit.html');
		}else{
			if(!$_POST['title'] || !$_POST['type'] || !$_POST['val']){
				$this->error('required_null');
			}
			
			$data = array(
				'title' => trim($_POST['title']),
				'title_key_' => trim($_POST['title_key_']),
				'type' => intval($_POST['type']),
				'val' => floatval($_POST['val']),
				'can_coupon' => intval($_POST['can_coupon']),
				'starttime' => strtotime($_POST['starttime']),
				'endtime' => strtotime($_POST['endtime']),
				'priority' => intval($_POST['priority']),
				'status' => intval($_POST['status']),
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('discount')->where("id=$id")->save($data);
			}else{
				$id = $this->mdata('discount')->add($data);
			}
			
			$this->ok('edit_success', url('discount/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->db->table('discount_set')->where("discount_id=$id")->delete();
			$this->mdata('discount')->where("id=$id")->delete();
		}
		$this->ok('delete_done', url('discount/index'));
	}
	
	public function set()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$this->view['data'] = $this->mdata('discount')->where("id=$id")->get();
		$this->view['list'] = $this->db->table('discount_set')->where("discount_id=$id")->getlist();
		$this->view['items'] = $this->model('discount')->set_item();
		$this->view['logic'] = $this->model('discount')->set_logic();
		$this->view['title'] = lang('condition_list');
		$this->view('discount/set.html');
	}
	
	public function addset()
	{
		if(!$_POST['submit']){
			$discount_id = intval($_GET['discount_id']);
			if(!$discount_id){
				$this->error('args_error');
			}
			
			$this->view['data'] = $this->mdata('discount')->where("id=$discount_id")->get();
			$this->view['items'] = $this->model('discount')->set_item();
			$this->view['logic'] = $this->model('discount')->set_logic();
			$this->view['discount_id'] = $discount_id;
			$this->view('discount/addset.html');
		}else{
			if(!$_POST['discount_id'] || !$_POST['item'] || !$_POST['logic'] || !$_POST['item_val']){
				$this->error('required_null');
			}
			
			$data = array(
				'discount_id' => intval($_POST['discount_id']),
				'item' => trim($_POST['item']),
				'logic' => trim($_POST['logic']),
				'item_val' => $_POST['logic']=='in' ? implode(',', $_POST['item_val']) : trim($_POST['item_val']),
			);
			
			$this->db->table('discount_set')->insert($data);
			$this->ok('edit_success', url('discount/set?id='.$data['discount_id']));
		}
	}
	
	public function delset()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$discount_id = $this->db->table('discount_set')->where("id=$id")->getval('discount_id');
		$this->db->table('discount_set')->where("id=$id")->delete();
		
		$this->ok('delete_done', url('discount/set?id='.$discount_id));
	}
	
	public function ajax_logic_list()
	{
		$item_id = trim($_GET['item_id']);
		$html = '<option value="0">-select-</option>';
		if($item_id){
			$logic = $this->model('discount')->set_logic();
			$bind = $this->model('discount')->bind();
			
			$logics = $bind[$item_id];
			foreach($logics as $k){
				$html .= '<option value="'.$k.'">'.$logic[$k].'</option>';
			}
		}
		echo $html;
	}
	
	public function ajax_val_list()
	{
		$item_id = trim($_GET['item_id']);
		$logic_id = trim($_GET['logic_id']);
		if(!$item_id || !$logic_id){
			return null;
		}
		
		$html = '';
		switch($item_id){
			case 'total':
			case 'qty':
			case 'weight':
				$html = '<input class="len2" name="item_val" type="text" value="">';
				break;
			case 'goods_id':
				$html = '<select class="len2" name="item_val[]"'.($logic_id == 'in' ? ' multiple="multiple"' : '').'>';
				$list = $this->db->table('goods')->field('goods_id,sku')->where("status=1")->getlist();
				foreach($list as $v){
					$html .= '<option value="'.$v['goods_id'].'">#'.$v['goods_id'].' '.$v['sku'].'</option>';
				}
				$html .= '</select>';
				break;
			case 'cate_id':
				$html = '<select class="len2" name="item_val[]"'.($logic_id == 'in' ? ' multiple="multiple"' : '').'>';
				$list = $this->model('goodscate')->getlist();
				foreach($list as $v){
					$html .= '<option value="'.$v['id'].'">'.$v['name'].'</option>';
					if($v['child']){
						foreach($v['child'] as $v2){
							$html .= '<option value="'.$v2['id'].'">&nbsp;'.$v2['name'].'</option>';
							if($v2['child']){
								foreach($v2['child'] as $v3){
									$html .= '<option value="'.$v3['id'].'">&nbsp;&nbsp;'.$v3['name'].'</option>';
								}
							}
						}
					}
				}
				$html .= '</select>';
				break;
			case 'group_id':
				$html = '<select class="len2" name="item_val[]"'.($logic_id == 'in' ? ' multiple="multiple"' : '').'>';
				$list = $this->mdata('user_group')->where('status=1')->getlist();
				foreach($list as $v){
					$html .= '<option value="'.$v['group_id'].'">'.$v['name'].'</option>';
				}
				$html .= '</select>';
				break;
			case 'currency':
				$html = '<select class="len2" name="item_val[]"'.($logic_id == 'in' ? ' multiple="multiple"' : '').'>';
				$list = &$this->model('common')->currencies();
				foreach($list as $v){
					$html .= '<option value="'.$v['code'].'">'.$v['code'].' '.$v['name'].'</option>';
				}
				$html .= '</select>';
				break;
		}
		echo $html;
	}
}

?>