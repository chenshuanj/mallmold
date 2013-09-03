<?php
/*
*	@upgrade.php
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

class upgrade extends model
{
	private $backup_dir = '/backup';
	private $server = 'http://www.mallmold.com/upgrade/';
	public $versions = array();
	public $local_version = 0;
	
	public function get_update()
	{
		$file = BASE_PATH .'/'.APP_NAME.'/version.php';
		if(!file_exists($file)){
			return 0;
		}
		
		include_once($file);
		$this->local_version = UPDATE;
		$url = $this->server.'upgrade.php?type=version&version='.$this->local_version;
		$str = $this->load('lib/http')->http_file_get($url);
		if(!$str || $str == 'Error'){
			return 0;
		}
		$versions = explode("\r\n", $str);
		foreach($versions as $k=>$v){
			if(!trim($v)){
				unset($versions[$k]);
			}
		}
		$this->versions = $versions;
		return $this->versions;
	}
	
	public function get_files($version)
	{
		$url = $this->server.'upgrade.php?type=files&version='.$version;
		$str = $this->load('lib/http')->http_file_get($url);
		if(!$str || $str == 'Error'){
			return array();
		}else{
			$arr = explode("\r\n", $str);
			foreach($arr as $k=>$v){
				if(!trim($v)){
					unset($arr[$k]);
				}
			}
			return $arr;
		}
	}
	
	public function get_database($version)
	{
		$url = $this->server.'upgrade.php?type=database&version='.$version;
		$str = $this->load('lib/http')->http_file_get($url);
		if(!$str || $str == 'Error'){
			return null;
		}else{
			return $str;
		}
	}
	
	public function getfile($version, $file)
	{
		$url = $this->server.'upgrade.php?type=getfile&version='.$version.'&file='.$file;
		$time = 1;
		while($time < 3){
			$str = $this->load('lib/http')->http_file_get($url, 15);
			if(!$str){
				$time++;
				sleep(1);
			}else{
				break;
			}
		}
		if($str == 'Error'){
			return null;
		}else{
			return $str;
		}
	}
	
	public function dif_versions()
	{
		$dif = array();
		$pass = 0;
		foreach($this->versions as $v){
			$arr = explode('-', $v);
			$update = trim($arr[0]);
			if($pass == 0){
				if($this->local_version < $update){
					$dif[] = $arr;
					$pass = 1;
				}
			}else{
				$dif[] = $arr;
			}
		}
		return $dif;
	}
	
	public function check_permission($file)
	{
		$path = BASE_PATH .'/'.$file;
		if(file_exists($path) && is_file($path)){
			return is_writable($path);
		}
		return true;
	}
	
	public function backup_file($file)
	{
		$path = BASE_PATH .'/'.$file;
		if(file_exists($path)){
			$backup_file = BASE_PATH . $this->backup_dir .'/'.$this->local_version.'/'.$file;
			$this->load('lib/dir')->checkdir($backup_file);
			copy($path, $backup_file);
		}
		return true;
	}
	
	public function update_file($version, $file)
	{
		$path = BASE_PATH .'/'.$file;
		$str = $this->getfile($version, $file);
		return file_put_contents($path, $str);
	}
	
	public function update_database($sqls)
	{
		$rows = explode("\n", $sqls);
		$sql = '';
		foreach($rows as $v){
			$v = trim($v);
			if(!$v){
				continue;
			}
			
			$code = substr($v, 0, 2);
			if($code == '--' || $code == '/*'){
				continue;
			}
			
			$sql .= ($sql ? "\n" : "").$v;
			
			$code = substr($v, -1, 1);
			if($code == ';'){
				$sql = str_replace('{PREFIX}', $this->db->prefix, $sql);
				$this->db->query($sql);
				$sql = '';
			}
		}
		return true;
	}
	
	public function update_version($version, $num)
	{
		$file = BASE_PATH .'/'.APP_NAME.'/version.php';
		$php = "<?php
define('UPDATE', '$version');
define('VERSION', '$num');
?>";
		return file_put_contents($file, $php);
	}
}
?>