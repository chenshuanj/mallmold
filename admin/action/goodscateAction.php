<?php
/*
*	@goodscateAction.php
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

class goodscateAction extends commonAction
{
	public function index()
	{
		$this->view['catelist'] = $this->model('goodscate')->getlist();
		$this->view['title'] = lang('category');
		$this->view('goodscate/index.html');
	}
	
	public function addcate()
	{
		$catelist = $this->model('goodscate')->getlist();
		if(!$_POST['submit']){
			if($_GET['id']){
				$id = intval($_GET['id']);
				$data = $this->mdata('goods_cate')->where("id=$id")->get();
			}else{
				$data = array();
			}
			
			$this->editor_header();
			$this->editor('description', $data['description'], 'description_txtkey_', $data['description_txtkey_'], 'article_desc');
			$this->editor_uploadbutton('image', $data['image'], 'goods_cate');
			
			$this->view['data'] = $data;
			$this->view['title'] = lang('edit_category');
			$this->view['catelist'] = $catelist;
			$this->view('goodscate/addcate.html');
		}else{
			if(!$_POST['name']){
				$this->error('name_null');
			}
			
			//if image is not uploaded
			$image = trim($_POST['image']);
			$image = $this->model('image')->check_img($image, 'goods_cate');
			
			$data = array(
				'pid' => intval($_POST['pid']),
				'name_key_' => trim($_POST['name_key_']),
				'name' => trim($_POST['name']),
				'description_txtkey_' => $_POST['description_txtkey_'],
				'description' => $_POST['description'],
				'meta_title_key_' => $_POST['meta_title_key_'],
				'meta_title' => trim($_POST['meta_title']),
				'meta_keywords_txtkey_' => $_POST['meta_keywords_txtkey_'],
				'meta_keywords' => trim($_POST['meta_keywords']),
				'meta_description_txtkey_' => $_POST['meta_description_txtkey_'],
				'meta_description' => trim($_POST['meta_description']),
				'urlkey' => to_url(trim($_POST['urlkey'])),
				'image' => $image,
				'sort_order' => intval($_POST['sort_order'])
			);
			
			if($_POST['id']){
				$cate_id = intval($_POST['id']);
				$this->mdata('goods_cate')->where("id=$cate_id")->save($data);
			}else{
				$cate_id = $this->mdata('goods_cate')->add($data);
			}
			$this->model('urlkey')->set_goodscate($cate_id, $data['urlkey']);
			$this->ok('edit_success', url('goodscate/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$n = $this->db->table('goods_cate')->where("pid=$id")->count();
			if($n > 0){
				$this->error('You can not delete a category that with sub-category');
				return;
			}
			
			$this->mdata('goods_cate')->where("id=$id")->delete();
			$this->model('urlkey')->del_goodscate($id);
			$this->ok('delete_done', url('goodscate/index'));
		}else{
			$this->error('args_error');
		}
	}
}

?>