<?php
/*
*	@attributeAction.php
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

class attributeAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->mdata('attribute')->getlist();
		$this->view('attribute/index.html');
	}
	
	public function add()
	{
		if(!$_POST['submit']){
			if($_GET['id']){
				$id = intval($_GET['id']);
				$data = $this->mdata('attribute')->where("attr_id=$id")->get();
				$this->view['data'] = $data;
			}
			
			$this->view['title'] = lang('editattribute');
			$this->view('attribute/add.html');
		}else{
			if(!$_POST['name']){
				$this->error('name_null');
			}
			
			$data = array(
				'name_key_' => trim($_POST['name_key_']),
				'name' => trim($_POST['name']),
				'can_filter' => intval($_POST['can_filter']),
				'sort_order' => intval($_POST['sort_order']),
				'status' => intval($_POST['status'])
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('attribute')->where("attr_id=$id")->save($data);
			}else{
				$this->mdata('attribute')->add($data);
			}
			$this->ok('edit_success', url('attribute/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->mdata('attribute_value')->where("attr_id=$id")->delete();
			$this->mdata('attribute')->where("attr_id=$id")->delete();
		}
		$this->ok('delete_done', url('attribute/index'));
	}
	
	public function value()
	{
		$id = intval($_GET['id']);
		$attr = $this->mdata('attribute')->where("attr_id=$id")->get();
		
		$this->view['attr'] = $attr;
		$this->view['list'] = $this->mdata('attribute_value')->where("attr_id=$id")->getlist();
		$this->view['title'] = $attr['name'] .' > '.lang('attribute_value');
		$this->view('attribute/value.html');
	}
	
	public function addvalue()
	{
		if(!$_POST['submit']){
			if($_GET['id']){
				$id = intval($_GET['id']);
				$data = $this->mdata('attribute_value')->where("av_id=$id")->get();
				$this->view['data'] = $data;
				$attr_id = $data['attr_id'];
			}else{
				$attr_id = $_GET['attr_id'];
			}
			$attr = $this->mdata('attribute')->where("attr_id=$attr_id")->get();
			$this->view['attr_id'] = $attr_id;
			$this->view['attr'] = $attr;
			$this->view['title'] = $attr['name'] .' > '.lang('attribute_edit');
			$this->view('attribute/addvalue.html');
		}else{
			if(!$_POST['title']){
				$this->error('title_null');
			}
			
			$data = array(
				'attr_id' => intval($_POST['attr_id']),
				'title_key_' => trim($_POST['title_key_']),
				'title' => trim($_POST['title']),
				'sort_order' => intval($_POST['sort_order'])
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('attribute_value')->where("av_id=$id")->save($data);
			}else{
				$this->mdata('attribute_value')->add($data);
			}
			$this->ok('edit_success', url('attribute/value?id='.$data['attr_id']));
		}
	}
	
	public function delvalue()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$attr_id = $this->db->table('attribute_value')->where("av_id=$id")->getval('attr_id');
			$this->mdata('attribute_value')->where("av_id=$id")->delete();
			$this->ok('delete_done', url('attribute/value?id='.$attr_id));
		}else{
			$this->error('args_error');
		}
	}
	
	public function extend()
	{
		$this->view['list'] = $this->mdata('extend')->getlist();
		$this->view['type'] = $this->model('extend')->type();
		$this->view('attribute/extend.html');
	}
	
	public function addextend()
	{
		if(!$_POST['submit']){
			if($_GET['id']){
				$id = intval($_GET['id']);
				$data = $this->mdata('extend')->where("extend_id=$id")->get();
				$this->view['data'] = $data;
				$this->view['values'] = $this->mdata('extend_val')->where("extend_id=$id")->order('sort_order asc')->getlist();
			}
			
			$this->view['type'] = $this->model('extend')->type();
			$this->view['title'] = lang('extend_edit');
			$this->view('attribute/addextend.html');
		}else{
			if(!$_POST['name']){
				$this->error('name_null');
			}
			
			$data = array(
				'name_key_' => trim($_POST['name_key_']),
				'name' => trim($_POST['name']),
				'type' => intval($_POST['type']),
				'sort_order' => intval($_POST['sort_order']),
				'status' => intval($_POST['status'])
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('extend')->where("extend_id=$id")->save($data);
			}else{
				$id = $this->mdata('extend')->add($data);
			}
			
			//val
			if($data['type']==2 || $data['type']==3){
				$val = $_POST['val'];
				$val_key_ = $_POST['val_key_'];
				$order = $_POST['order'];
				if($val && is_array($val)){
					$val_at = array();
					$val_id = array();
					$val_list = $this->mdata('extend_val')->where("extend_id=$id")->getlist();
					foreach($val_list as $v){
						$val_at[$v['val']] = $v['sort_order'];
						$val_id[$v['val']] = $v['id'];
					}
					
					foreach($val as $k=>$v){
						$v = trim($v);
						if(!empty($v)){
							if(!$val_at[$v]){
								$data = array(
									'extend_id' => $id,
									'val' => $v,
									'val_key_' => $val_key_[$k],
									'sort_order' => intval($order[$k]),
								);
								$this->mdata('extend_val')->add($data);
							}else{
								if(intval($order[$k]) != $val_at[$v]){
									$this->db->table('extend_val')->where("extend_id=$id and val='$v'")->update(array('sort_order'=>$sort_order[$k]));
								}
								unset($val_id[$v]);
							}
						}
					}
					
					if($val_id){
						foreach($val_id as $v){
							$this->mdata('extend_val')->where("extend_id=$id and id=$v")->delete();
						}
					}
				}
			}
			
			$this->ok('edit_success', url('attribute/extend'));
		}
	}
	
	public function delextend()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->mdata('goods_extend')->where("extend_id=$id")->delete();
			$this->mdata('extend_val')->where("extend_id=$id")->delete();
			$this->mdata('extend')->where("extend_id=$id")->delete();
		}
		$this->ok('delete_done', url('attribute/extend'));
	}
}

?>