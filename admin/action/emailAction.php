<?php
/*
*	@emailAction.php
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

class emailAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->mdata('email_template')->getlist();
		$this->view['title'] = lang('emailtpl');
		$this->view('email/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$name = trim($_GET['name']);
			if(!$name){
				$this->error('args_error');
			}
			
			$data = $this->mdata('email_template')->where("name='$name'")->get();
			
			$this->editor_header();
			$this->editor('content', $data['content'], 'content_txtkey_', $data['content_txtkey_'], 'other');
			
			$this->view['data'] = $data;
			$this->view['title'] = lang('edit_tpl');
			$this->view('email/edit.html');
		}else{
			$name = trim($_POST['name']);
			if(!$name){
				$this->error('args_error');
			}
			
			$data = array(
				'title' => trim($_POST['title']),
				'title_key_' => trim($_POST['title_key_']),
				'content' => trim($_POST['content']),
				'content_txtkey_' => trim($_POST['content_txtkey_']),
			);
			$this->mdata('email_template')->where("name='$name'")->save($data);
			
			//delete template for update
			$file = $this->db->table('email_template')->where("name='$name'")->getval('path');
			$path = BASE_PATH .'/admin/template/default/notice/'.$file;
			if(file_exists($path)){
				unlink($path);
			}
			
			$this->ok('edit_success', url('email/index'));
		}
	}
	
	public function log()
	{
		$total = $this->db->table('email_log')->count();
		$this->pager($total);
		
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$list = $this->db->table('email_log')->limit($limit)->getlist();
		foreach($list as $k=>$v){
			$list[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
		}
		
		$this->view['list'] = $list;
		$this->view['title'] = lang('emaillog');
		$this->view('email/log.html');
	}
	
	public function view_log()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		$email = $this->db->table('email_log')->where("id=$id")->get();
		$email['time'] = date('Y-m-d H:i:s', $email['time']);
		$this->view['email'] = $email;
		$this->view['title'] = lang('view_email');
		$this->view('email/view_log.html');
	}
	
	public function resend()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$email = $this->db->table('email_log')->where("id=$id")->get();
		if(!$email){
			$this->error('access_error');
		}
		
		if($email['status'] == 1){
			$this->error('had_send');
		}
		
		$res = $this->model('notice')->mail($email['email'], $email['title'], $email['contents'], $id);
		if($res){
			$this->ok('send_success', url('email/view_log?id='.$id));
		}else{
			$this->error('send_failed');
		}
	}
}

?>