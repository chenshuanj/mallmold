<?php
/*
*	@uploadAction.php
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

class uploadAction extends commonAction
{
	public $save_dir = 'upload/';
	public $fkey = 'imgFile';
	protected $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
	public $max_size = 1024000;
	
	public function select()
	{
		$base_path = BASE_PATH .'/'. $this->save_dir;
		$dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
		
		if (empty($_GET['path'])) {
			$current_path = $base_path .$dir_name . '/';
			$current_url = PHP_PATH .$this->save_dir .$dir_name . '/';
			$current_dir_path = '';
			$moveup_dir_path = '';
		} else {
			$current_path = $base_path .$dir_name . '/' . $_GET['path'];
			$current_url = PHP_PATH . $this->save_dir .$dir_name . '/' .$_GET['path'];
			$current_dir_path = $_GET['path'];
			$moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
		}
		
		$GLOBALS['upload_file_order'] = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);
		
		if (preg_match('/\.\./', $current_path)) {
			echo 'Access is not allowed.';
			exit;
		}
		
		if (!preg_match('/\/$/', $current_path)) {
			echo 'Parameter is not valid.';
			exit;
		}
		
		if (!file_exists($current_path) || !is_dir($current_path)) {
			echo $current_path;
			echo '<br/>Directory does not exist.';
			exit;
		}
		
		$file_list = array();
		if ($handle = opendir($current_path)) {
			$i = 0;
			while (false !== ($filename = readdir($handle))) {
				if ($filename{0} == '.') continue;
				$file = $current_path . $filename;
				if (is_dir($file)) {
					$file_list[$i]['is_dir'] = true;
					$file_list[$i]['has_file'] = (count(scandir($file)) > 2);
					$file_list[$i]['filesize'] = 0;
					$file_list[$i]['is_photo'] = false;
					$file_list[$i]['filetype'] = '';
				} else {
					$file_list[$i]['is_dir'] = false;
					$file_list[$i]['has_file'] = false;
					$file_list[$i]['filesize'] = filesize($file);
					$file_list[$i]['dir_path'] = '';
					$file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
					$file_list[$i]['is_photo'] = in_array($file_ext, $this->ext_arr);
					$file_list[$i]['filetype'] = $file_ext;
				}
				$file_list[$i]['filename'] = $filename;
				$file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file));
				$i++;
			}
			closedir($handle);
		}
		
		usort($file_list, 'cmp_func');
		
		$result = array(
			'moveup_dir_path' => $moveup_dir_path,
			'current_dir_path' => $current_dir_path,
			'current_url' => $current_url,
			'total_count' => count($file_list),
			'file_list' => $file_list,
		);
		
		header('Content-type: application/json; charset=UTF-8');
		echo json_encode($result);
	}
	
	public function image()
	{
		$type = $_GET['type'] ? trim($_GET['type']) : 'other';
		
		if($_GET['setting_id']){
			$setting_id = trim($_GET['setting_id']);
		}else{
			$setting_id = '0';
		}
		
		$upload = $this->upload();
		$url = $this->model('image')->add($upload['url'], $type, $setting_id);
		header('Content-type: text/html; charset=UTF-8');
		echo json_encode(array('error' => 0, 'url' => $url, 'label' => $upload['label']));
	}
	
	protected function upload()
	{
		$save_dir = BASE_PATH .'/'.$this->save_dir;
		$save_url = PHP_PATH .$this->save_dir;
		
		if(!empty($_FILES[$this->fkey]['error'])){
			$this->upload_error($_FILES[$this->fkey]['error']);
		}
		
		if(empty($_FILES) === false){
			$file_name = $_FILES[$this->fkey]['name'];
			$tmp_name = $_FILES[$this->fkey]['tmp_name'];
			$file_size = $_FILES[$this->fkey]['size'];
			
			if(!$file_name){
				$this->act_error(lang('nonefile_selected'));
			}
			
			$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = strtolower(trim($file_ext));
			
			if(in_array($file_ext, $this->ext_arr) === false) {
				$this->act_error(lang('unsupported_extensions'));
			}
			
			if(@is_dir($save_dir) === false){
				$this->act_error(lang('dir_notexist'));
			}
			if(@is_writable($save_dir) === false){
				$this->act_error(lang('dir_cannot_write'));
			}
			if(@is_uploaded_file($tmp_name) === false){
				$this->act_error(lang('upload_failed'));
			}
			if($file_size > $this->max_size){
				$this->act_error(lang('file_limited'));
			}
			
			$dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
			if($dir_name){
				$save_dir .= $dir_name . "/";
				$save_url .= $dir_name . "/";
				if(!file_exists($save_dir)){
					mkdir($save_dir);
				}
			}
			
			$save_dir .= date("Ym") . "/";
			$save_url .= date("Ym") . "/";
			if(!file_exists($save_dir)){
				mkdir($save_dir);
			}
			
			$new_file_name = $file_name;
			$file_path = $save_dir . $new_file_name;
			$name = implode('.', $temp_arr);
			$n = 1;
			while(file_exists($file_path)){
				$new_file_name = $name.'-'.$n.'.'.$file_ext;
				$file_path = $save_dir . $new_file_name;
				$n++;
			}
			
			if (move_uploaded_file($tmp_name, $file_path) === false) {
				$this->act_error(lang('upload_failed'));
			}
			@chmod($file_path, 0755);
			$file_url = $save_url . $new_file_name;
			
			return array('url' => $file_url, 'label' => $name);
		}
	}
	
	protected function upload_error($n)
	{
		switch($n){
			case '1':
				$error = lang('php_limited');
				break;
			case '2':
				$error = lang('form_limited');
				break;
			case '3':
				$error = lang('upload_incomplete');
				break;
			case '4':
				$error = lang('unselect_pic');
				break;
			case '6':
				$error = lang('none_tmpdir');
				break;
			case '7':
				$error = lang('writetoharddisk_error');
				break;
			case '8':
				$error = 'File upload stopped by extension。';
				break;
			case '999':
			default:
				$error = lang('unknown_error');
		}
		$this->act_error($error);
	}
	
	protected function act_error($msg)
	{
		header('Content-type: text/html; charset=UTF-8');
		echo json_encode(array('error' => 1, 'message' => $msg));
		exit;
	}
	
}

?>