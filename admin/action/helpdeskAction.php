<?php
/*
*	@helpdeskAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require Action('common');

class helpdeskAction extends commonAction
{
	public function index()
	{
		if(isset($_POST['email'])){
			$email = trim($_POST['email']);
			$_SESSION['helpdes_index_email'] = $email;
		}else{
			$email = $_SESSION['helpdes_index_email'];
		}
		
		$where = '';
		if($email){
			$where = "email like '%$email%'";
		}
		
		$total = $this->db->table('helpdesk')->where($where)->count();
		$this->pager($total);
		
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$list = $this->db->table('helpdesk')->where($where)->limit($limit)->order('time desc')->getlist();
		foreach($list as $k=>$v){
			$list[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
		}
		
		$this->view['list'] = $list;
		$this->view['email'] = $email;
		$this->view['title'] = lang('helpdesk');
		$this->view('helpdesk/list.html');
	}
	
	public function ticket()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$ticket = $this->db->table('helpdesk')->where("id=$id")->get();
		$department_id = $ticket['department_id'];
		$department = $this->mdata('helpdesk_department')->where("id=$department_id")->get();
		
		$reply = $this->db->table('helpdesk_reply')->where("ticket_id=$id")->order('time asc')->getlist();
		foreach($reply as $k=>$v){
			$reply[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
		}
		
		$response = $this->mdata('helpdesk_response')->getlist();
		
		$this->view['ticket'] = $ticket;
		$this->view['department'] = $department;
		$this->view['reply'] = $reply;
		$this->view['response'] = $response;
		$this->view['title'] = lang('helpdesk');
		$this->view('helpdesk/ticket.html');
	}
	
	public function reply()
	{
		$id = intval($_POST['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		if(!$_POST['reply']){
			$this->error('content_null');
		}
		
		$data = array(
			'ticket_id' => $id,
			'message' => trim($_POST['reply']),
			'mail' => (isset($_POST['mail']) ? 1 : 0),
			'time' => time(),
		);
		
		$this->db->table('helpdesk_reply')->insert($data);
		
		if($data['mail'] == 1){
			$this->notice($id, $data['message']);
		}
		
		$this->ok('edit_success', url('helpdesk/ticket?id='.$id));
	}
	
	public function department()
	{
		$this->view['department'] = $this->mdata('helpdesk_department')->getlist();
		$this->view['title'] = lang('helpdesk');
		$this->view('helpdesk/department.html');
	}
	
	public function department_edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->mdata('helpdesk_department')->where("id=$id")->get();
				$this->view['data'] = $data;
			}
			
			$this->view('helpdesk/department_edit.html');
		}else{
			if(!$_POST['name']){
				$this->error('name_null');
			}
			
			$data = array(
				'name_key_' => trim($_POST['name_key_']),
				'name' => trim($_POST['name']),
				'sort_order' => intval($_POST['sort_order']),
				'status' => intval($_POST['status']),
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('helpdesk_department')->where("id=$id")->save($data);
			}else{
				$id = $this->mdata('helpdesk_department')->add($data);
			}
			
			$this->ok('edit_success', url('helpdesk/department'));
		}
	}
	
	public function responses()
	{
		$this->view['response'] = $this->mdata('helpdesk_response')->getlist();
		$this->view['title'] = lang('helpdesk');
		$this->view('helpdesk/responses.html');
	}
	
	public function get_response()
	{
		$id = intval($_POST['id']);
		$data = $this->mdata('helpdesk_response')->where("id=$id")->get();
		echo $data['content'];
	}
	
	public function response_edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->mdata('helpdesk_response')->where("id=$id")->get();
				$this->view['data'] = $data;
			}
			
			$this->editor_header();
			$this->editor('content', $data['content'], 'content_txtkey_', $data['content_txtkey_'], 'other');
			
			$this->view('helpdesk/response_edit.html');
		}else{
			if(!$_POST['title']){
				$this->error('title_null');
			}
			if(!$_POST['content']){
				$this->error('content_null');
			}
			
			$data = array(
				'title_key_' => trim($_POST['title_key_']),
				'title' => trim($_POST['title']),
				'content_txtkey_' => trim($_POST['content_txtkey_']),
				'content' => $_POST['content'],
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('helpdesk_response')->where("id=$id")->save($data);
			}else{
				$id = $this->mdata('helpdesk_response')->add($data);
			}
			
			$this->ok('edit_success', url('helpdesk/responses'));
		}
	}
	
	public function response_delete()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->mdata('helpdesk_response')->where("id=$id")->delete();
		}
		$this->ok('delete_done', url('helpdesk/responses'));
	}
	
	private function notice($id, $message)
	{
		$ticket = $this->db->table('helpdesk')->where("id=$id")->get();
		return $this->model('notice')->mail($ticket['email'], 'RE:'.$ticket['title'], $message);
	}
}

?>