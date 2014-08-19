<?php
/*
*	@navAction.php
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

class navAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->mdata('nav')->getlist();
		$this->view['title'] = lang('nav');
		$this->view['types'] = $this->types();
		$this->view('nav/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->mdata('nav')->where("id=$id")->get();
				$this->view['data'] = $data;
			}
			$this->view['types'] = $this->types();
			$this->view('nav/edit.html');
		}else{
			if(!$_POST['title'] || !$_POST['type'] || !$_POST['url']){
				$this->error('required_null');
			}
			
			$data = array(
				'title_key_' => trim($_POST['title_key_']),
				'title' => trim($_POST['title']),
				'type' => intval($_POST['type']),
				'url' => trim($_POST['url']),
				'sort_order' => intval($_POST['sort_order']),
				'status' => intval($_POST['status'])
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('nav')->where("id=$id")->save($data);
			}else{
				$this->mdata('nav')->add($data);
			}
			
			$this->ok('edit_success', url('nav/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->mdata('nav')->where("id=$id")->delete();
		}
		$this->ok('delete_done', url('nav/index'));
	}
	
	private function types()
	{
		return array(
			1 => lang('middle'),
			2 => lang('top'),
			3 => lang('bottom'),
		);
	}
}

?>