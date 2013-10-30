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
			$this->error('404 not found');
			return;
		}
		
		$goods = $this->model('goods')->get($goods_id);
		if(!$goods){
			$this->error('404 not found');
			return;
		}
		
		//add click
		$this->model('statistic')->add($goods_id, 'click');
		
		//images type
		$this->view['img_sign'] = $this->model('image')->getsignbyid($this->setting['goods_view_sid']);
		$this->view['show_sign'] = $this->model('image')->getsignbyid($this->setting['goods_imgs_small_sid']);
		$this->view['show_sign_big'] = $this->model('image')->getsignbyid($this->setting['goods_imgs_big_sid']);
		
		//comment
		$goods['score'] = $this->model('comment')->get_score($goods_id);
		$this->view['comments'] = $this->model('comment')->get_comments($goods_id);
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
		$group_id = $this->db->table('goods')->where("goods_id=$goods_id")->getval('group_id');
		$summarys_list = $this->model('comment')->get_summarys($group_id);
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
			//$this->ok();
			header('Location: '.url('goods/index?goods_id='.$goods_id));
		}
	}
}

?>