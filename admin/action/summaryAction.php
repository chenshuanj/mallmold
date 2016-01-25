<?php
/*
*	@summaryAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require Action('common');

class summaryAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->mdata('summary')->getlist();
		$this->view['title'] = lang('summary');
		$this->view('summary/index.html');
	}
	
	public function set()
	{
		$change = trim($_GET['change']);
		$id = intval($_GET['id']);
		if(!$id || !$change){
			$this->error('args_error');
		}
		
		if($change == 'status'){
			$status = $this->db->table('summary')->where("id=$id")->getval('status');
			$status = $status == 1 ? 0 : 1;
			$this->db->table('summary')->where("id=$id")->update(array('status'=>$status));
		}
		
		$this->ok('edit_success', url('summary/index'));
	}
	
	public function add()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->mdata('summary')->where("id=$id")->get();
				$this->view['data'] = $data;
			}
			$this->view('summary/add.html');
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
				$this->mdata('summary')->where("id=$id")->save($data);
			}else{
				$id = $this->mdata('summary')->add($data);
			}
			
			$this->ok('edit_success', url('summary/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->mdata('summary')->where("id=$id")->delete();
		}
		$this->ok('delete_done', url('summary/index'));
	}
}

?>