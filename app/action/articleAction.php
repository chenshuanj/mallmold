<?php
/*
*	@articleAction.php
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

class articleAction extends commonAction
{
	public function cate()
	{
		$cate_id = $this->model('urlkey')->getid('cate_id');
		if(!$cate_id){
			$this->_404();
			return;
		}
		
		$cate = $this->model('article')->get_cate($cate_id);
		if(!$cate){
			$this->_404();
			return;
		}
		
		$list = $this->model('article')->get_list($cate_id);
		
		$this->view['html_title'] = $cate['name'];
		$this->view['map'] = array(array('title' => $cate['name']));
		$this->view['cate_id'] = $cate_id;
		$this->view['list'] = $list;
		$this->view['cates_list'] = $this->model('article')->cates_list();
		$this->view('article/list.html');
	}
	
	public function index()
	{
		$article_id = $this->model('urlkey')->getid('article_id');
		if(!$article_id){
			$this->_404();
			return;
		}
		
		$article = $this->model('article')->get_article($article_id);
		if(!$article){
			$this->_404();
			return;
		}
		
		$map = array();
		$cate_id = $article['cate_id'];
		if($cate_id){
			$cate = $this->model('article')->get_cate($cate_id);
			$map[] = array(
				'title' => $cate['name'],
				'url' => $this->model('urlkey')->geturl('cate_id', $cate['cate_id'], $cate['urlkey']),
			);
		}
		$map[] = array('title' => $article['title']);
		
		$this->view['html_title'] = $article['title'];
		$this->view['map'] = $map;
		$this->view['article'] = $article;
		$this->view['cates_list'] = $this->model('article')->cates_list();
		$this->view('article/view.html');
	}
}

?>