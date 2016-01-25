<?php
/*
*	@indexAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require Action('common');

class indexAction extends commonAction
{
	public function index()
	{
		$data = array();
		$data['time'] = date('H:i:s');
		$data['date'] = date('Y-m-d');
		
		$time = strtotime($data['date']);
		//new order
		$data['neworder'] = $this->db->table('order')->where("status>0 and addtime>=$time")->count();
		$data['newuser'] = $this->db->table('user')->where("reg_time>=$time")->count();
		
		//top5
		$this->view['clicklist'] = $this->model('statistic')->get_top5('click');
		$this->view['cartlist'] = $this->model('statistic')->get_top5('cart');
		$this->view['buylist'] = $this->model('statistic')->get_top5('buy');
		$this->view['attribute'] = $this->model('statistic')->get_attr_top5();
		
		//keyword
		$this->view['keywords'] = $this->db->table('keywords')->order("search_num desc")->limit(5)->getlist();
		
		//version
		$file = BASE_PATH .'/version.php';
		if(file_exists($file)){
			include($file);
			$this->view['version'] = VERSION;
		}
		
		$this->view['data'] = $data;
		$this->view['title'] = 'welcome';
		$this->view('index.html');
	}
	
	public function cachelist()
	{
		$this->view['title'] = lang('clearcache');
		$this->view('cache.html');
	}
	
	public function clear()
	{
		$type = trim($_GET['type']);
		$setting = &$this->model('common')->setting();
		$frontend = $setting['frontend'];
		$dir = BASE_PATH ."/$frontend/cache";
		
		if($type=='data' || $type=='all'){
			$this->load('lib/dir')->deldir($dir.'/data');
		}
		
		if($type=='tpl' || $type=='all'){
			$this->load('lib/dir')->deldir($dir.'/template');
		}
		
		$this->ok('edit_success', url('index/cachelist'));
	}
}

?>