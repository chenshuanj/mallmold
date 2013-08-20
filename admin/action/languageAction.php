<?php
/*
*	@languageAction.php
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

class languageAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->db->table('language')->getlist();
		$this->view['title'] = lang('language');
		$this->view('language/index.html');
	}
	
	public function set()
	{
		$change = trim($_GET['change']);
		$id = intval($_GET['id']);
		if(!$id || !$change){
			$this->error('args_error');
		}
		
		if($change == 'status'){
			$language = $this->db->table('language')->where("id=$id")->get();
			$status = $language['status'] == 1 ? 0 : 1;
			
			//check if default
			$main_lang = $this->model('common')->main_lang();
			if($status == 0 && $language['code']==$main_lang){
				$this->error('mainlang_disable');
				return;
			}
			
			$this->db->table('language')->where("id=$id")->update(array('status'=>$status));
		}
		
		$this->ok('edit_success', url('language/index'));
	}
	
	public function edit()
	{
		$code = strtolower(trim($_GET['code']));
		$setting = &$this->model('common')->setting();
		$frontend = $setting['frontend'];
		
		$file = BASE_PATH ."/$frontend/language/$code.php";
		if(!file_exists($file)){
			$this->error('cannot_find_file');
		}
		
		if(!$_POST['submit']){
			$data = include $file;
			
			$this->view['code'] = $code;
			$this->view['data'] = $data;
			$this->view['title'] = lang('edit_lang');
			$this->view('language/edit.html');
		}else{
			$data = $_POST['data'];
			$str = "<?php\r\nreturn ".var_export($data, true)."\r\n?>";
			file_put_contents($file, $str);
			$this->ok('edit_success', url('language/index'));
		}
	}
	
	public function add()
	{
		if(!$_POST['submit']){
			$this->view['list'] = $this->db->table('language_code')->getlist();
			$this->view['title'] = lang('add_lang');
			$this->view('language/add.html');
		}else{
			$code = trim(strtolower($_POST['code']));
			if(!$code){
				$this->error('unselect_lang');
			}
			
			//if exists
			$n = $this->db->table('language')->where("code='$code'")->count();
			if($n>0){
				$this->error('had_addlang');
			}
			
			$row = $this->db->table('language_code')->where("code='$code'")->get();
			$data = array(
				'code' => $code,
				'name' => $row['name'],
				'status' => 0,
			);
			$this->db->table('language')->insert($data);
			
			//Dictionary
			$sql = 'ALTER TABLE `'.$this->db->tbname('dict').'` ADD `dict_val_'.$code.'` VARCHAR( 255 ) NULL DEFAULT NULL';
			$this->db->query($sql);
			$sql = 'CREATE TABLE `'.$this->db->tbname('dict_text_'.$code).'` LIKE `'.$this->db->tbname('dict_text').'`;';
			$this->db->query($sql);
			
			//language file
			$setting = &$this->model('common')->setting();
			$frontend = $setting['frontend'];
			$main_lang = $this->model('common')->main_lang();
			
			//backend
			$admin_cp_file = BASE_PATH ."/admin/language/$main_lang.php";
			$admin_file = BASE_PATH ."/admin/language/$code.php";
			if(!file_exists($admin_file)){
				copy($admin_cp_file, $admin_file);
			}
			
			//frontend
			$cp_file = BASE_PATH ."/$frontend/language/$main_lang.php";
			$file = BASE_PATH ."/$frontend/language/$code.php";
			if(!file_exists($file)){
				copy($cp_file, $file);
			}
			
			$this->ok('edit_success', url('language/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$language = $this->db->table('language')->where("id=$id")->get();
		$main_lang = $this->model('common')->main_lang();
		if($language['code']==$main_lang){
			$this->error('lang_cannotdel');
		}
		
		if(!$_POST['submit']){
			$this->view['language'] = $language;
			$this->view['title'] = lang('del_lang');
			$this->view('language/del.html');
		}else{
			$code = $language['code'];
			$sql = 'ALTER TABLE `'.$this->db->tbname('dict').'` DROP `dict_val_'.$code.'` ';
			$this->db->query($sql);
			$sql = 'DROP TABLE `'.$this->db->tbname('dict_text_'.$code).'`';
			$this->db->query($sql);
			
			$setting = &$this->model('common')->setting();
			$frontend = $setting['frontend'];
			$admin_file = BASE_PATH ."/admin/language/$code.php";
			$file = BASE_PATH ."/$frontend/language/$code.php";
			@unlink($admin_file);
			@unlink($file);
			
			$this->db->table('language')->where("id=$id")->delete();
			
			$this->ok('edit_success', url('language/index'));
		}
	}
}

?>