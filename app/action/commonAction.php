<?php
/*
*	@commonAction.php
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

class commonAction extends action
{
	protected $setting;
	
	public function __construct()
    {
		parent::__construct();
		
		$this->init();
		$this->view_header();
		$this->view_footer();
	}
	
	private function init()
	{
		require(APP_PATH .'lib/common.php');
		$this->setting = &$this->model('common')->setting();
		
		//set lang
		$current_lang = $this->model('visitor')->visitor_lang();
		cookie('lang', $current_lang);
		$this->config['LAN_NAME'] = $current_lang;
		$this->setting = $this->model('dictionary')->getdict($this->setting);
		
		//set cur
		$last_cur = cookie('cur');
		$current_cur = $this->model('visitor')->visitor_currency();
		if($last_cur != $current_cur){
			cookie('cur', $current_cur);
			$this->model('cart')->change_cart_cur();
		}
		
		//set template
		$template = $this->model('visitor')->visitor_tpl();
		if($template){
			$this->set_template($template);
		}
		
		$this->view['current_lang'] = $current_lang;
		$this->view['current_cur'] = $current_cur;
		$this->view['current_symbol'] = &$this->model('common')->current_symbol();
	}
	
	private function view_header()
	{
		//html title
		$this->view['html_title'] = $this->setting['web_name'];
		
		//languages,currencies select
		$this->view['languages'] = &$this->model('common')->languages();
		$this->view['currencies'] = &$this->model('common')->currencies();
		
		$args = $_GET;
		$url = ($args['c'] ? $args['c'] : 'index').'/'.($args['a'] ? $args['a'] : 'index');
		unset($args['set_lang']);
		unset($args['set_cur']);
		unset($args['c']);
		unset($args['a']);
		unset($args[$_SERVER['REQUEST_URI']]);
		$query = http_build_query($args);
		$this->view['current_url_lang'] = url($url.'?'.($query ? $query.'&' : '').'set_lang=');
		$this->view['current_url_cur'] = url($url.'?'.($query ? $query.'&' : '').'set_cur=');
		
		//login status
		$is_login = 0;
		if($this->model('user')->is_login()){
			$is_login = 1;
			$user = $this->model('user')->get($_SESSION['user_id']);
			$this->view['user_name'] = $user['firstname'].' '.$user['lastname'];
		}
		$this->view['is_login'] = $is_login;
		
		//cart status
		$this->view['cart_num'] = $this->model('cart')->get_num();
		
		//top nav
		$this->view['top_nav'] = $this->model('common')->nav(2);
		
		//logo
		$this->view['web_logo'] = $this->setting['web_logo'];
		
		//main nav
		$this->view['main_nav'] = $this->model('common')->nav(1);
		
		//all catalogs
		$this->view['catalogs'] = $this->model('catalog')->get_catelist();
		
		$is_home = 0;
		if(MODULE == 'index' && ACTION == 'index'){
			$is_home = 1;
		}
		$this->view['is_home'] = $is_home;
		
		//top block
		$this->view['top_tel'] = $this->model('common')->block('top_tel');
	}
	
	private function view_footer()
	{
		//foot_article
		$this->view['foot_article'] = $this->model('common')->block('foot_article');
		
		//bottom nav
		$this->view['bottom_nav'] = $this->model('common')->nav(3);
		
		//copyright
		$this->view['copyright'] = $this->model('common')->block('copyright');
		
		//bottom logo
		$this->view['bottom_logo'] = $this->setting['btm_logo'];
	}
	
	protected function error($msg='', $url='')
    {
		$url = $url ? '"'.$url.'"' : '-1';
		$this->view['url'] = $url;
		$this->view['msg'] = lang($msg);
		$this->view['title'] = 'Error';
		$this->view('error/error.html');
	}
}

?>