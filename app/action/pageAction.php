<?php
/*
*	@pageAction.php
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

class pageAction extends commonAction
{
	public function index()
	{
		$page_id = $this->model('urlkey')->getid('page_id');
		if(!$page_id){
			$this->_404();
			return;
		}
		
		$page = $this->model('mdata')->table('pages')->where("id=$page_id")->get();
		if(!$page){
			$this->_404();
			return;
		}
		
		//parse tag
		$page['content'] = $this->parse($page['content']);
		
		$this->view['html_title'] = $page['meta_title'] ? $page['meta_title'] : $page['title'];
		$this->view['meta_description'] = $page['meta_description'];
		$this->view['meta_keywords'] = $page['meta_keywords'];
		
		//$this->view['map'] = array(array('title' => $page['title']));
		$this->view['page'] = $page;
		$this->view('page/index.html');
	}
	
	/*
	{tag type="goods/list" where="***" limit="*" order="time" tpl="tag/goods_list.html"}
	{tag type="article/list" cate_id="*" limit="*" tpl="tag/article_list.html"}
	{tag type="slider" id="***" tpl="tag/slider.html"}
	{tag type="block" id="***"}
	*/
	private function parse($str)
	{
		$str = preg_replace_callback("/\{tag\s+(.+?)\}/is", 'self::callback', $str);
		return $str;
	}
	
	private function callback($matches)
	{
		return $this->build($matches[1]);
	}
	
	private function build($tag)
	{
		$tag = trim(stripslashes($tag));
		if(!$tag)
			return null;
		
		preg_match_all('/\s(.+?)="(|.+?)"/is', ' '.$tag, $rs);
		$args = array();
		foreach($rs[1] as $k=>$v){
			$v = trim($v);
			$args[$v] = trim($rs[2][$k]);
		}
		
		return $this->convert($args);
	}
	
	private function convert($args)
	{
		if(!$args['type']){
			return false;
		}
		
		switch($args['type'])
		{
			case 'goods/list':
				$this->view['tag_list'] = $this->model('goods')->getlist($args['where'], $args['order'], intval($args['limit']));
				$tpl = $args['tpl'] ? $args['tpl'] : 'tag/goods_list.html';
				$content = $this->view($tpl, 0);
				return $content;
			case 'article/list':
				$this->view['tag_list'] = $this->model('article')->get_list(intval($args['cate_id']), intval($args['limit']));
				$tpl = $args['tpl'] ? $args['tpl'] : 'tag/article_list.html';
				$content = $this->view($tpl, 0);
				return $content;
			case 'slider':
				$this->view['tag_slider'] = $this->model('slider')->get($args['id']);
				$tpl = $args['tpl'] ? $args['tpl'] : 'tag/slider.html';
				$content = $this->view($tpl, 0);
				return $content;
			case 'block':
				$tag = $this->model('mdata')->table('block')->where("code='".$args['id']."'")->get();
				return $tag['content'];
		}
	}
}

?>