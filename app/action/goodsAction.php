<?php
/*
*	@goodsAction.php
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

class goodsAction extends commonAction
{
	public function index()
	{
		$goods_id = $this->model('urlkey')->getid('goods_id');
		if(!$goods_id){
			$this->_404();
			return;
		}
		
		$goods = $this->model('goods')->get($goods_id);
		if(!$goods){
			$this->_404();
			return;
		}
		
		//add click
		$this->model('statistic')->add($goods_id, 'click');
		
		//images type
		$this->view['img_sign'] = $this->model('image')->getsignbyid('goods_view_sid');
		$this->view['show_sign'] = $this->model('image')->getsignbyid('goods_imgs_small_sid');
		$this->view['show_sign_big'] = $this->model('image')->getsignbyid('goods_imgs_big_sid');
		
		//cross sell
		$this->view['cross_sell'] = $this->model('goods')->get_cross_sell($goods_id);
		
		//comment
		$this->view['comments'] = $this->model('comment')->get_comments($goods_id);
		$this->view['comments_num'] = count($this->view['comments']);
		$this->view['summarys'] = $this->model('comment')->get_summarys($goods['group_id']);
		$this->view['goods'] = $goods;
		$this->view['catelist'] = $this->model('catalog')->get_catelist(0);
		$this->view['attributes'] = $this->model('catalog')->get_attributes();
		$this->view['html_title'] = $goods['meta_title'] ? $goods['meta_title'] : $goods['title'];
		$this->view['meta_description'] = $goods['meta_description'];
		$this->view['meta_keywords'] = $goods['meta_keywords'];
		
		$map = $this->model('catalog')->goods_map($goods_id);
		$map[] = array('title'=>$goods['title']);
		$this->view['map'] = $map;
		
		$this->view('goods/view.html');
	}
	
	public function comment()
	{
		$goods_id = intval($_GET['goods_id']);
		if(!$goods_id){
			$this->error();
			return;
		}
		
		$username = trim($_POST['username']);
		$content = $this->load('lib/filter')->filter_html(trim($_POST['content']));
		if(!$username || !$username){
			$this->error();
			return;
		}
		if(!$this->load('lib/filter')->is_username($username)){
			$this->error('Username can only consist of letters, numbers, spaces or single quotes composition');
			return;
		}
		
		$score = $_POST['score'];
		$summarys = array();
		$goods = $this->db->table('goods')->where("goods_id=$goods_id")->get();
		$summarys_list = $this->model('comment')->get_summarys($goods['group_id']);
		foreach($summarys_list as $v){
			$id = $v['id'];
			if($score[$id]){
				$summarys[$id] = intval($score[$id]);
			}
		}
		
		$id = $this->model('comment')->add($goods_id, $username, $content, $summarys);
		if(!$id){
			$this->error();
			return;
		}else{
			$url = $this->model('urlkey')->geturl('goods_id', $goods['goods_id'], $goods['urlkey']);
			
			//$this->ok();
			header('Location: '.url($url));
		}
	}
	
	public function ajax_get_comment()
	{
		$goods_id = intval($_POST['goods_id']);
		$page = $_POST['page'] ? intval($_POST['page']) : 2;
		$list_num = $this->model('comment')->list_num;
		$limit = ($page - 1)*$list_num .','.$list_num;
		
		$this->view['comments'] = $this->model('comment')->get_comments($goods_id, $limit);
		$content = $this->view('goods/comments.html', 0);
		echo $content ? $content : 0;
	}
}

?>