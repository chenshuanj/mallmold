<?php
/*
*	@articleAction.php
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

class articleAction extends commonAction
{
	public function index()
	{
		$total = $this->db->table('article')->count();
		$this->pager($total);
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		$this->view['list'] = $this->mdata('article')->order('article_id desc')->limit($limit)->getlist();
		$this->view['title'] = lang('articlelist');
		$this->view('article/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['article_id']);
			if($id){
				$data = $this->mdata('article')->where("article_id=$id")->get();
				$this->view['data'] = $data;
			}
			
			$this->view['catelist'] = $this->mdata('article_cate')->getlist();
			
			$this->editor_header();
			$this->editor('content', $data['content'], 'content_txtkey_', $data['content_txtkey_'], 'article_desc');
			$this->editor_uploadbutton('image', $data['image'], 'article_img');
			
			$this->view('article/edit.html');
		}else{
			if(!$_POST['title']){
				$this->error('title_null');
			}
			
			//if image is not uploaded
			$image = trim($_POST['image']);
			$image = $this->model('image')->check_img($image, 'article_img');
			
			$data = array(
				'title_key_' => trim($_POST['title_key_']),
				'title' => trim($_POST['title']),
				'cate_id' => intval($_POST['cate_id']),
				'urlkey' => to_url(trim($_POST['urlkey'])),
				'image' => $image,
				'content_txtkey_' => trim($_POST['content_txtkey_']),
				'content' => $_POST['content'],
				'sort_order' => intval($_POST['sort_order']),
				'status' => intval($_POST['status'])
			);
			
			if($_POST['article_id']){
				$id = intval($_POST['article_id']);
				$this->mdata('article')->where("article_id=$id")->save($data);
			}else{
				$id = $this->mdata('article')->add($data);
			}
			
			$this->model('urlkey')->set_article($id, $data['urlkey']);
			$this->ok('edit_success', url('article/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['article_id']);
		if($id>0){
			$this->mdata('article')->where("article_id=$id")->delete();
			$this->model('urlkey')->del_article($id);
		}
		$this->ok('delete_done', url('article/index'));
	}
	
	public function cate()
	{
		$this->view['list'] = $this->mdata('article_cate')->getlist();
		$this->view['title'] = lang('articlecate');
		$this->view('article/cate.html');
	}
	
	public function editcate()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['cate_id']);
			if($id){
				$data = $this->mdata('article_cate')->where("cate_id=$id")->get();
				$this->view['data'] = $data;
			}
			
			$this->editor_header();
			$this->editor_uploadbutton('image', $data['image'], 'articlecate_img');
			
			$this->view('article/editcate.html');
		}else{
			if(!$_POST['name']){
				$this->error('name_null');
			}
			
			$data = array(
				'name_key_' => trim($_POST['name_key_']),
				'name' => trim($_POST['name']),
				'urlkey' => to_url(trim($_POST['urlkey'])),
				'image' => trim($_POST['image']),
				'sort_order' => intval($_POST['sort_order']),
				'status' => intval($_POST['status'])
			);
			
			if($_POST['cate_id']){
				$id = intval($_POST['cate_id']);
				$this->mdata('article_cate')->where("cate_id=$id")->save($data);
			}else{
				$id = $this->mdata('article_cate')->add($data);
			}
			
			$this->model('urlkey')->set_articlecate($id, $data['urlkey']);
			$this->ok('edit_success', url('article/cate'));
		}
	}
	
	public function delcate()
	{
		$id = intval($_GET['cate_id']);
		if($id>0){
			$this->mdata('article_cate')->where("cate_id=$id")->delete();
			$this->model('urlkey')->del_articlecate($id);
		}
		$this->ok('delete_done', url('article/cate'));
	}
}

?>