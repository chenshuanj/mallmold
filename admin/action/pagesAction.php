<?php


require Action('common');

class pagesAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->mdata('pages')->getlist();
		$this->view['title'] = lang('pages');
		$this->view('pages/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->mdata('pages')->where("id=$id")->get();
				$this->view['data'] = $data;
			}
			
			$this->editor_header();
			$this->editor('content', $data['content'], 'content_txtkey_', $data['content_txtkey_'], 'article_desc');
			$this->editor_uploadbutton('image', $data['image'], 'article_img');
			
			$this->view('pages/edit.html');
		}else{
			if(!$_POST['title']){
				$this->error('title_null');
			}
			
			$data = array(
				'title_key_' => trim($_POST['title_key_']),
				'title' => trim($_POST['title']),
				'urlkey' => to_url(trim($_POST['urlkey'])),
				'content_txtkey_' => trim($_POST['content_txtkey_']),
				'content' => $_POST['content'],
				'sort_order' => intval($_POST['sort_order']),
				'image' => trim($_POST['image']),
				'meta_title_key_' => $_POST['meta_title_key_'],
				'meta_title' => trim($_POST['meta_title']),
				'meta_keywords_txtkey_' => $_POST['meta_keywords_txtkey_'],
				'meta_keywords' => trim($_POST['meta_keywords']),
				'meta_description_txtkey_' => $_POST['meta_description_txtkey_'],
				'meta_description' => trim($_POST['meta_description']),
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('pages')->where("id=$id")->save($data);
			}else{
				$id = $this->mdata('pages')->add($data);
			}
			$this->model('urlkey')->set_page($id, $data['urlkey']);
			$this->ok('edit_success', url('pages/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->mdata('pages')->where("id=$id")->delete();
			$this->model('urlkey')->del_page($id);
		}
		$this->ok('delete_done', url('pages/index'));
	}
	
	public function block()
	{
		$this->view['list'] = $this->mdata('block')->getlist();
		$this->view['title'] = lang('block');
		$this->view('pages/block.html');
	}
	
	public function editblock()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->mdata('block')->where("id=$id")->get();
				$this->view['data'] = $data;
			}
			
			$this->editor_header();
			$this->editor('content', $data['content'], 'content_txtkey_', $data['content_txtkey_'], 'article_desc');
			
			$this->view('pages/editblock.html');
		}else{
			if(!$_POST['code']){
				$this->error('sign_null');
			}
			
			//check code
			$code = trim($_POST['code']);
			$row = $this->db->table('block')->where("code='$code'")->get();
			if($row && $row['id'] != $_POST['id']){
				$this->error('code_repeated');
			}
			
			$data = array(
				'content_txtkey_' => trim($_POST['content_txtkey_']),
				'content' => trim($_POST['content']),
				'code' => $code,
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('block')->where("id=$id")->save($data);
			}else{
				$this->mdata('block')->add($data);
			}
			
			$this->ok('edit_success', url('pages/block'));
		}
	}
	
	public function delblock()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->mdata('block')->where("id=$id")->delete();
		}
		$this->ok('delete_done', url('pages/block'));
	}
}

?>