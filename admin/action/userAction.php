<?php
/*
*	@userAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require Action('common');

class userAction extends commonAction
{
	public function index()
	{
		if(isset($_POST['email'])){
			$email = trim($_POST['email']);
			$_SESSION['user_index_email'] = $email;
		}else{
			$email = $_SESSION['user_index_email'];
		}
		
		$where = '';
		if($email){
			$where = "email like '%$email%'";
		}
		
		$total = $this->db->table('user')->where($where)->count();
		$this->pager($total);
		
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$list = $this->db->table('user')->where($where)->order('reg_time desc')->limit($limit)->getlist();
		foreach($list as $k=>$v){
			$list[$k]['reg_time'] = date('Y-m-d H:i:s', $v['reg_time']);
		}
		
		$this->view['list'] = $list;
		$this->view['email'] = $email;
		$this->view['title'] = lang('userlist');
		$this->view('user/index.html');
	}
	
	public function show()
	{
		$id = intval($_GET['user_id']);
		if($id){
			$data = $this->db->table('user')->where("user_id=$id")->get();
			$data['time'] = date('Y-m-d H:i:s', $data['reg_time']);
			
			if($data['group_id']){
				$group = $this->mdata('user_group')->where("group_id={$data['group_id']}")->get();
				$data['group'] = $group['name'];
			}else{
				$data['group'] = lang('none_group');
			}
			
			//order
			$order = $this->db->table('order')->where("user_id=$id")->getlist();
			foreach($order as $k=>$v){
				$order[$k]['time'] = date('Y-m-d H:i:s', $v['addtime']);
			}
			$this->view['order'] = $order;
			$symbols = array();
			$currencies = &$this->model('common')->currencies();
			foreach($currencies as $v){
				$symbols[$v['code']] = $v['symbol'];
			}
			$this->view['order_status'] = $this->model('order')->order_status();
			$this->view['symbols'] = $symbols;
			
			//address
			$address = $this->db->table('user_address')->where("user_id=$id")->getlist();
			foreach($address as $k=>$v){
				$address[$k]['country'] = $this->db->table('country')->where("id=".$v['country_id'])->getval('name');
				$address[$k]['state'] = $this->db->table('region')->where("region_id=".$v['region_id'])->getval('name');
			}
			$this->view['address'] = $address;
			
			$this->view['data'] = $data;
			$this->view('user/show.html');
		}else{
			$this->error('args_error');
		}
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['user_id']);
			if($id){
				$data = $this->db->table('user')->where("user_id=$id")->get();
				$this->view['data'] = $data;
				
				$this->view['grouplist'] = $this->mdata('user_group')->getlist();
				$this->view('user/edit.html');
			}else{
				$this->error('args_error');
			}
		}else{
			$id = intval($_POST['user_id']);
			if(!$id){
				$this->error('args_error');
			}
			
			$data = array(
				'group_id' => intval($_POST['group_id']),
				'score' => intval($_POST['score']),
			);
			if($_POST['password']){
				if(!$_POST['repassword'] || $_POST['repassword'] != $_POST['password']){
					$this->error('pswd_unmatch');
				}else{
					$salt = $this->db->table('user')->where("user_id=$id")->getval('salt');
					$data['password'] = md5($salt.$_POST['password']);
				}
			}
			
			$this->db->table('user')->where("user_id=$id")->update($data);
			$this->ok('edit_success', url('user/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['user_id']);
		if($id>0){
			$this->db->table('user')->where("user_id=$id")->delete();
			//other
			$this->db->table('user_address')->where("user_id=$id")->delete();
		}
		$this->ok('delete_done', url('user/index'));
	}
	
	public function group()
	{
		$this->view['list'] = $this->mdata('user_group')->getlist();
		$this->view['title'] = lang('usergroup');
		$this->view('user/group.html');
	}
	
	public function editgroup()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['group_id']);
			if($id){
				$data = $this->mdata('user_group')->where("group_id=$id")->get();
				$this->view['data'] = $data;
			}
			$this->view('user/editgroup.html');
		}else{
			if(!$_POST['name']){
				$this->error('required_null');
			}
			
			$data = array(
				'name_key_' => trim($_POST['name_key_']),
				'name' => trim($_POST['name']),
				'spending' => floatval($_POST['spending']),
				'status' => intval($_POST['status'])
			);
			
			if($_POST['group_id']){
				$id = intval($_POST['group_id']);
				$this->mdata('user_group')->where("group_id=$id")->save($data);
			}else{
				$id = $this->mdata('user_group')->add($data);
			}
			
			$this->ok('edit_success', url('user/group'));
		}
	}
	
	public function delgroup()
	{
		$id = intval($_GET['group_id']);
		if($id>0){
			$this->mdata('user_group')->where("group_id=$id")->delete();
		}
		$this->ok('delete_done', url('user/group'));
	}
}

?>