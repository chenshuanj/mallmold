<?php
/*
*	@optionAction.php
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

class optionAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->mdata('option')->getlist();
		$this->view['title'] = lang('option');
		$this->view('option/index.html');
	}
	
	public function add()
	{
		if(!$_POST['submit']){
			if($_GET['id']){
				$id = intval($_GET['id']);
				$data = $this->mdata('option')->where("op_id=$id")->get();
				$this->view['data'] = $data;
			}
			
			$this->view['title'] = lang('edit_option');
			$this->view('option/add.html');
		}else{
			if(!$_POST['name']){
				$this->error('name_null');
			}
			
			$data = array(
				'name_key_' => trim($_POST['name_key_']),
				'name' => trim($_POST['name']),
				'sort_order' => intval($_POST['sort_order']),
				'status' => intval($_POST['status'])
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('option')->where("op_id=$id")->save($data);
			}else{
				$cate_id = $this->mdata('option')->add($data);
			}
			$this->ok('edit_success', url('option/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->mdata('option')->where("op_id=$id")->delete();
		}
		$this->ok('delete_done', url('option/index'));
	}
}

?>