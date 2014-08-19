<?php
/*
*	@groupAction.php
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

require Action('common');

class groupAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->mdata('group')->getlist();
		$this->view['title'] = lang('product_group');
		$this->view('group/index.html');
	}
	
	public function add()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			
			$this->view['catelist'] = $this->model('goodscate')->getlist();
			$this->view['attribute'] = $this->mdata('attribute')->getlist();
			$this->view['extend'] = $this->mdata('extend')->getlist();
			$this->view['option'] = $this->mdata('option')->getlist();
			$this->view['summary'] = $this->mdata('summary')->getlist();
			
			$group_cate = $group_attr = $group_extend = $group_option = $group_summary = array();
			if($id){
				$data = $this->mdata('group')->where("id=$id")->get();
				$this->view['data'] = $data;
				
				$group_cate_list = $this->db->table('group_cate')->where("group_id=$id")->getlist();
				$group_cate = $this->list2array($group_cate_list, 'cate_id');
				
				$group_attr_list = $this->db->table('group_attr')->where("group_id=$id")->getlist();
				$group_attr = $this->list2array($group_attr_list, 'attr_id');
				
				$group_extend_list = $this->db->table('group_extend')->where("group_id=$id")->getlist();
				$group_extend = $this->list2array($group_extend_list, 'extend_id');
				
				$group_option_list = $this->db->table('group_option')->where("group_id=$id")->getlist();
				$group_option = $this->list2array($group_option_list, 'op_id');
				
				$group_summary_list = $this->db->table('group_summary')->where("group_id=$id")->getlist();
				$group_summary = $this->list2array($group_summary_list, 'summary_id');
			}
			
			$this->view['group_cate'] = $group_cate;
			$this->view['group_attr'] = $group_attr;
			$this->view['group_extend'] = $group_extend;
			$this->view['group_option'] = $group_option;
			$this->view['group_summary'] = $group_summary;
			$this->view('group/add.html');
		}else{
			if(!$_POST['name']){
				$this->error('name_null');
			}
			
			$data = array(
				'name_key_' => trim($_POST['name_key_']),
				'name' => trim($_POST['name']),
				'status' => intval($_POST['status'])
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('group')->where("id=$id")->save($data);
			}else{
				$id = $this->mdata('group')->add($data);
			}
			
			$cate_id = $_POST['cate_id'];
			$attr_id = $_POST['attr_id'];
			$extend_id = $_POST['extend_id'];
			$op_id = $_POST['op_id'];
			$summary_id = $_POST['summary_id'];
			
			$this->db->table('group_cate')->where("group_id=$id")->delete();
			if($cate_id){
				foreach($cate_id as $v){
					$this->db->table('group_cate')->insert(array('group_id'=>$id, 'cate_id'=>$v));
				}
			}
			$this->db->table('group_attr')->where("group_id=$id")->delete();
			if($attr_id){
				foreach($attr_id as $v){
					$this->db->table('group_attr')->insert(array('group_id'=>$id, 'attr_id'=>$v));
				}
			}
			$this->db->table('group_extend')->where("group_id=$id")->delete();
			if($extend_id){
				foreach($extend_id as $v){
					$this->db->table('group_extend')->insert(array('group_id'=>$id, 'extend_id'=>$v));
				}
			}
			$this->db->table('group_option')->where("group_id=$id")->delete();
			if($op_id){
				foreach($op_id as $v){
					$this->db->table('group_option')->insert(array('group_id'=>$id, 'op_id'=>$v));
				}
			}
			$this->db->table('group_summary')->where("group_id=$id")->delete();
			if($summary_id){
				foreach($summary_id as $v){
					$this->db->table('group_summary')->insert(array('group_id'=>$id, 'summary_id'=>$v));
				}
			}
			$this->ok('edit_success', url('group/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			//check goods
			$n = $this->db->table('goods')->where("group_id=$id")->count();
			if($n > 0){
				$this->error('This group have products, can not be deleted');
				return false;
			}
			
			$this->db->table('group_cate')->where("group_id=$id")->delete();
			$this->db->table('group_attr')->where("group_id=$id")->delete();
			$this->db->table('group_extend')->where("group_id=$id")->delete();
			$this->db->table('group_option')->where("group_id=$id")->delete();
			$this->db->table('group_summary')->where("group_id=$id")->delete();
			$this->mdata('group')->where("id=$id")->delete();
			
			$this->ok('delete_done', url('group/index'));
		}else{
			$this->error('args_error');
		}
	}
	
	private function list2array($list, $key)
	{
		$arr = array();
		foreach($list as $v){
			$arr[] = $v[$key];
		}
		return $arr;
	}
}

?>