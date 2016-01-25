<?php
/*
*	@hostAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require Action('common');

class hostAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->db->table('host')->getlist();
		
		$countrys = array();
		$country_list = $this->db->table('country')->where('status=1')->getlist();
		foreach($country_list as $v){
			$countrys[$v['id']] = $v['name'];
		}
		
		$this->view['countrys'] = $countrys;
		$this->view['title'] = lang('hostlist');
		$this->view('host/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->db->table('host')->where("id=$id")->get();
				$this->view['data'] = $data;
			}
			
			$this->view['countrys'] = $this->db->table('country')->where('status=1')->getlist();
			$this->view['language'] = &$this->model('common')->languages();
			$this->view['currency'] = &$this->model('common')->currencies();
			
			$this->view('host/edit.html');
		}else{
			if(!$_POST['host']){
				$this->error('required_null');
			}
			
			$data = array(
				'host' => trim($_POST['host']),
				'template' => trim($_POST['template']),
				'bind_country' => trim($_POST['bind_country']),
				'bind_language' => trim($_POST['bind_language']),
				'bind_currency' => trim($_POST['bind_currency']),
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->db->table('host')->where("id=$id")->update($data);
			}else{
				$id = $this->db->table('host')->insert($data);
			}
			
			$this->ok('edit_success', url('host/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->db->table('host')->where("id=$id")->delete();
		}
		$this->ok('delete_done', url('host/index'));
	}
}

?>