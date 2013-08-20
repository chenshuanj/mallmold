<?php
/*
*	@manageAction.php
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

class manageAction extends commonAction
{
	public function account()
	{
		$m_id = $_SESSION['m_id'];
		$my = $this->db->table('admin')->where("id=$m_id")->get();
		
		$where = '';
		if($my['group_id'] != 0){
			$where = "id=$m_id";
		}
		
		$this->view['list'] = $this->mdata('admin')->where($where)->getlist();
		$this->view['my'] = $my;
		$this->view['title'] = lang('account');
		$this->view('manage/account.html');
	}
	
	public function edit()
	{
		$id = intval($_GET['id']);
		
		$m_id = $_SESSION['m_id'];
		$my = $this->db->table('admin')->where("id=$m_id")->get();
		
		if($id != $my['id'] && $my['group_id'] != 0){
			$this->error('permission_error');
		}
		
		if($id > 0){
			if($id != $my['id']){
				$data = $this->db->table('admin')->where("id=$id")->get();
			}else{
				$data = $my;
			}
			$this->view['data'] = $data;
		}
		
		$this->view['my'] = $my;
		$this->view['title'] = lang('edit_account');
		$this->view('manage/edit.html');
	}
	
	public function update()
	{
		$m_id = $_SESSION['m_id'];
		$my = $this->db->table('admin')->where("id=$m_id")->get();
		
		$id = $_POST['id'];
		$name = isset($_POST['name']) ? trim($_POST['name']) : '';
		$password = $_POST['password'];
		$repassword = $_POST['repassword'];
		
		if(!$id && $my['group_id'] != 0){
			$this->error('permission_error');
		}
		if($my['group_id'] != 0 && $id != $my['id']){
			$this->error('permission_error');
		}
		if(($id == $my['id'] || $my['group_id'] != 0) && isset($_POST['status'])){
			$this->error('data_error');
		}
		if($id == $my['id'] && !$password){
			$this->error('data_error');
		}
		if(!$id && (!$name || !$password)){
			$this->error('data_error');
		}
		if($password && $password != $repassword){
			$this->error('pswd_unmatch');
		}
		
		$data = array();
		if(!$id){
			$data['group_id'] = 1;
			$data['name'] = $name;
		}
		if($password){
			$data['salt'] = $this->model('login')->create_salt();
			$data['pswd'] = $this->model('login')->encrypt($password, $data['salt']);
		}
		if(isset($_POST['status'])){
			$data['status'] = intval($_POST['status']);
		}
		
		if($id){
			$this->db->table('admin')->where("id=$id")->update($data);
		}else{
			$this->db->table('admin')->insert($data);
		}
		
		$this->ok('edit_success', url('manage/account'));
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$m_id = $_SESSION['m_id'];
		$my = $this->db->table('admin')->where("id=$m_id")->get();
		if($my['group_id'] != 0){
			$this->error('permission_error');
		}
		if($id == $m_id){
			$this->error('permission_error');
		}
		
		$this->db->table('admin')->where("id=$id")->delete();
		$this->ok('delete_done', url('manage/account'));
	}
}

?>