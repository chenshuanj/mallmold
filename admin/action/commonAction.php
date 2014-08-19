<?php
/*
*	@commonAction.php
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

class commonAction extends action
{
	public function __construct()
    {
		parent::__construct();
		
		require(APP_PATH .'model/functions.php');
		
		if(!$this->model('login')->checklogin()){
			header('Location: '.url('admin/login'));
			exit;
		}
		
		$args = $_GET;
		$url = $args['c'].'/'.$args['a'];
		unset($args['set_lang']);
		unset($args['c']);
		unset($args['a']);
		$query = http_build_query($args);
		$this->view['current_url_lang'] = url($url.'?'.($query ? $query.'&' : '').'set_lang=');
		
		$m_id = $_SESSION['m_id'];
		$rs = $this->db->table('admin')->where("id=$m_id")->get();
		$this->view['username'] = $rs['name'];
		
		$languages = &$this->model('common')->languages();
		if($_GET['set_lang']){
			$lang = trim($_GET['set_lang']);
			cookie('admin_lang', $lang);
		}elseif(!cookie('admin_lang')){
			//main language
			$lang = $this->model('common')->main_lang();
			cookie('admin_lang', $lang);
		}else{
			$lang = cookie('admin_lang');
			$check = 0;
			foreach($languages as $v){
				if($lang == $v['code']){
					$check = 1;
					break;
				}
			}
			if($check == 0){
				$lang = $this->model('common')->main_lang();
				cookie('admin_lang', $lang);
			}
		}
		
		$GLOBALS['config']['LAN_NAME'] = $lang;
		$this->view['lang'] = $lang;
		$this->view['select_lang'] = $languages;
		
		//default_cur
		$this->view['main_cur'] = $this->model('common')->main_cur();
		$this->view['current_symbol'] = &$this->model('common')->current_symbol();
		
		//weight_unit
		$this->view['weight_unit'] = $this->model('common')->weight_unit();
		
		//error_report
		if(MODULE != 'report'){
			$n = $this->db->table('error_report')->where('status=0')->count();
			if($n > 0){
				$this->view['error_num'] = $n;
				$this->view['error_msg'] = $this->db->table('error_report')->order('time desc')->getval('message');
			}
		}
	}
	
	protected function mdata($table)
	{
		return $this->model('mdata')->table($table);
	}
	
	public function pager($total)
    {
		$pager = $this->load('lib/page');
		$ss_key = 'p_'.md5($pager->geturl());
		$page = $pager->getpage();
		if($page > 0){
			$_SESSION[$ss_key] = $page;
		}elseif($_SESSION[$ss_key]){
			$page = $_SESSION[$ss_key];
		}
		
		$pager->page = $page;
		$this->view['pager'] = $pager->pager($total);
	}
	
	public function error($msg, $url='')
    {
		$url = $url ? '"'.$url.'"' : '-1';
		$this->view['url'] = $url;
		$this->view['msg'] = lang($msg);
		$this->view['title'] = 'Error';
		$this->view('error.html');
		exit;
	}
	
	public function ok($msg, $url='')
    {
		$this->view['url'] = $url;
		$this->view['msg'] = lang($msg);
		$this->view['time'] = 1;
		$this->view['title'] = 'Success';
		$this->view('ok.html');
		exit;
	}
	
	protected function editor_header()
	{
		$this->view['headers'] = 'editor/header.html';
	}
	
	protected function editor($name, $value, $namekey, $keyval, $imgtype, $width=600, $height=240)
	{
		$this->view['editor_name'] = $name;
		$this->view['editor_value'] = $value;
		if($namekey){
			$this->view['editor_namekey'] = $namekey;
			$this->view['editor_keyval'] = $keyval;
		}
		$this->view['editor_imgtype'] = $imgtype;
		$this->view['editor_width'] = $width;
		$this->view['editor_height'] = $height;
		$this->view['session_id'] = session_id();
	}
	
	protected function editor_uploadbutton($name, $value, $imgtype, $setting_id=0)
	{
		$this->view['editor_button_name'][] = $name;
		$this->view['editor_button_value'][] = $value;
		$this->view['editor_button_imgtype'][] = $imgtype;
		$this->view['image_setting_id'][] = $setting_id;
	}
	
	protected function editor_multiuploadbutton($name, $label, $label_key, array $value, $imgtype)
	{
		$this->view['editor_multibutton_n'] = $value ? count($value) : 1;
		$this->view['editor_multibutton_name'] = $name;
		$this->view['editor_multibutton_label'] = $label;
		$this->view['editor_multibutton_label_key'] = $label_key;
		$this->view['editor_multibutton_value'] = $value;
		$this->view['editor_multibutton_imgtype'] = $imgtype;
		$this->view['session_id'] = session_id();
	}
}

?>