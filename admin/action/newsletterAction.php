<?php
/*
*	@newsletterAction.php
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

class newsletterAction extends commonAction
{
	public function index()
	{
		$total = $this->db->table('newsletter')->count();
		$this->pager($total);
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		$list = $this->mdata('newsletter')->order('addtime desc')->limit($limit)->getlist();
		foreach($list as $k=>$row){
			$list[$k]['read'] = $this->db->table('newsletter_send')->where('newsletter_id='.$row['newsletter_id'].' and read_status=1')->count();
		}
		
		$this->view['list'] = $list;
		$this->view['title'] = lang('Newsletter');
		$this->view('newsletter/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$newsletter_id = intval($_GET['newsletter_id']);
			if($newsletter_id){
				$data = $this->mdata('newsletter')->where("newsletter_id=$newsletter_id")->get();
				$this->view['data'] = $data;
			}
			
			$this->editor_header();
			$this->editor('content', $data['content'], 'content_txtkey_', $data['content_txtkey_'], 'other');
			
			$this->view('newsletter/edit.html');
		}else{
			if(!$_POST['title']){
				$this->error('title_null');
			}
			if(!$_POST['content']){
				$this->error("Content can't be null");
			}
			
			$data = array(
				'title_key_' => trim($_POST['title_key_']),
				'title' => trim($_POST['title']),
				'content_txtkey_' => trim($_POST['content_txtkey_']),
				'content' => trim($_POST['content']),
				'enable' => intval($_POST['enable']),
			);
			
			if($_POST['newsletter_id']){
				$newsletter_id = intval($_POST['newsletter_id']);
				$this->mdata('newsletter')->where("newsletter_id=$newsletter_id")->save($data);
			}else{
				$data['addtime'] = time();
				$data['sn'] = $this->model('newsletter')->create_sn();
				$newsletter_id = $this->mdata('newsletter')->add($data);
			}
			
			if($data['enable'] == 1){
				$this->model('newsletter')->add_event($newsletter_id);
			}
			
			$this->ok('edit_success', url('newsletter/index'));
		}
	}
	
	public function del()
	{
		$newsletter_id = intval($_GET['newsletter_id']);
		if($newsletter_id>0){
			$this->mdata('newsletter')->where("newsletter_id=$newsletter_id")->delete();
			$this->db->table('newsletter_send')->where("newsletter_id=$newsletter_id")->delete();
		}
		$this->ok('delete_done', url('newsletter/index'));
	}
	
	public function subscriber()
	{
		$total = $this->db->table('newsletter_subscriber')->count();
		$this->pager($total);
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$this->view['list'] = $this->db->table('newsletter_subscriber')->order('addtime desc')->limit($limit)->getlist();
		$this->view['title'] = lang('Subscriber');
		$this->view('newsletter/subscriber.html');
	}
	
	public function subscriber_del()
	{
		$subscriber_id = intval($_GET['subscriber_id']);
		if($subscriber_id>0){
			$this->db->table('newsletter_subscriber')->where("subscriber_id=$subscriber_id")->delete();
			$this->db->table('newsletter_send')->where("subscriber_id=$subscriber_id")->delete();
		}
		$this->ok('delete_done', url('newsletter/subscriber'));
	}
}

?>