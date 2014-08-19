<?php
/*
*	@dictionaryAction.php
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

class dictionaryAction extends commonAction
{
	public function index()
	{
		$where = '';
		$code = $_POST['code'];
		$keyword = trim($_POST['keyword']);
		if($code && $keyword){
			$where = "dict_val_$code like '%$keyword%'";
		}else{
			$code = cookie('admin_lang');
		}
		
		$total = $this->db->table('dict')->where($where)->count();
		$this->pager($total);
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$this->view['list'] = $this->db->table('dict')->where($where)->limit($limit)->getlist();
		$this->view['language'] = &$this->model('common')->languages();
		$this->view['main_lang'] = $this->model('common')->main_lang();
		$this->view['colspan'] = count($this->view['language']) + 2;
		$this->view['code'] = $code;
		$this->view['keyword'] = $keyword;
		$this->view('dictionary/index.html');
	}
	
	public function edit()
	{
		$language = &$this->model('common')->languages();
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->db->table('dict')->where("id=$id")->get();
				$this->view['data'] = $data;
			}
			$this->view['language'] = $language;
			$this->view('dictionary/edit.html');
		}else{
			extract($_POST);
			if(!$id){
				$this->error('args_error');
			}
			
			$data = array();
			foreach($language as $v){
				$key = 'dict_val_'.$v['code'];
				$data[$key] = ${$key};
			}
			$this->db->table('dict')->where("id=$id")->update($data);
			
			//save to cache
			if($this->model('cache')->enable == true){
				$sign = $this->db->table('dict')->where("id=$id")->getvar('dict_key');
				foreach($language as $v){
					$key = 'dict_val_'.$v['code'];
					$value = ${$key};
					$this->model('cache')->set($v['code'], $sign, $value);
				}
			}
			
			$this->ok('edit_success', url('dictionary/index'));
		}
	}
	
	public function text()
	{
		$where = '';
		$code = $_POST['code'];
		$keyword = trim($_POST['keyword']);
		if($code && $keyword){
			$where = "content like '%$keyword%'";
		}else{
			$code = cookie('admin_lang');
		}
		$table = 'dict_text_'.$code;
		
		$total = $this->db->table($table)->where($where)->count();
		$this->pager($total);
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$lang_list = &$this->model('common')->languages();
		$lang = array();
		foreach($lang_list as $v){
			$lang[$v['code']] = $v;
		}
		
		$list = $this->db->table($table)->where($where)->limit($limit)->getlist();
		foreach($list as $k=>$v){
			foreach($lang as $c=>$l){
				if($c == $code){
					$list[$k][$c] = mb_substr(strip_tags($v['content']), 0, 8, 'utf-8').'...';
				}else{
					$str = $this->db->table('dict_text_'.$c)->where("text_key='{$v['text_key']}'")->getval('content');
					$list[$k][$c] = $str ? mb_substr(strip_tags(trim($str)), 0, 8, 'utf-8').'...' : '[null]';
				}
			}
		}
		
		$this->view['language'] = $lang;
		$this->view['colspan'] = count($lang) + 2;
		$this->view['list'] = $list;
		$this->view['code'] = $code;
		$this->view['keyword'] = $keyword;
		$this->view('dictionary/text.html');
	
	}
	
	public function textedit()
	{
		$language = &$this->model('common')->languages();
		
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			$code = trim($_GET['code']);
			if(!$id || !$code){
				$this->error('args_error');
			}
			
			$table = 'dict_text_'.$code;
			$data = $this->db->table($table)->where("id=$id")->get();
			foreach($language as $v){
				$lang = $v['code'];
				if($lang != $code){
					$table = 'dict_text_'.$lang;
					$data[$lang] = $this->db->table($table)->where("text_key='{$data['text_key']}'")->getval('content');
				}else{
					$data[$lang] = $data['content'];
				}
			}
			$this->view['data'] = $data;
			$this->view['language'] = $language;
			$this->view('dictionary/textedit.html');
		}else{
			$text_key = trim($_POST['text_key']);
			if(!$text_key){
				$this->error('args_error');
			}
			
			foreach($language as $v){
				$lang = $v['code'];
				$table = 'dict_text_'.$lang;
				$n = $this->db->table($table)->where("text_key='$text_key'")->count();
				if($n > 0){
					$this->db->table($table)->where("text_key='$text_key'")->update(array('content'=>$_POST[$lang]));
				}elseif($_POST[$lang]){
					$data = array(
						'text_key' => $text_key,
						'content' => $_POST[$lang],
					);
					$this->db->table($table)->insert($data);
				}
			}
			
			//save to cache
			if($this->model('cache')->enable == true){
				foreach($language as $v){
					$key = $v['code'];
					$value = $_POST[$key];
					$this->model('cache')->set($key, $text_key, $value);
				}
			}
			
			$this->ok('edit_success', url('dictionary/text'));
		}
		
	}
	
	public function ajax()
	{
		$key = trim($_GET['key']);
		$type = $_GET['type'] ? trim($_GET['type']) : '_key_';
		if(!$key){
			echo lang('args_error');
		}else{
			$this->view['type'] = $type;
			$this->view['key'] = $key;
			$this->view['data'] = $this->model('dict')->get_vals($type, $key);
			$this->view('dictionary/ajax'.$type.'.html');
		}
	}
	
	public function ajaxsave()
	{
		$data = $_POST;
		$type = $data['type'];
		$key = $data['key'];
		if($type && $key){
			$this->model('dict')->set_vals($type, $key, $data);
			$status = 1;
		}else{
			$status = 0;
		}
		header('Content-type: text/html; charset=utf-8');
		echo $status;
	}
}
?>