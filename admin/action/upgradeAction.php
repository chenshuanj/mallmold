<?php
/*
*	@upgradeAction.php
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

class upgradeAction extends commonAction
{
	public function index()
	{
		if($this->check() == 0){
			$this->error('No need to update');
		}
		$dif = $this->model('upgrade')->dif_versions();
		$update_files = $update_sqls = $update_time = array();
		$ver = $this->model('upgrade')->local_version;
		foreach($dif as $version){
			$num = trim($version[1]);
			$v = trim($version[0]);
			$update_time[$num] = $ver;
			$ver = $v;
			$update_files[$num] = $this->model('upgrade')->get_files($v);
			
			$sqls = $this->model('upgrade')->get_database($v);
			$update_sqls[$num] = str_replace(";\r\n", ";\r\n<br/><br/>", $sqls);
		}
		
		$this->view['update_files'] = $update_files;
		$this->view['update_sqls'] = $update_sqls;
		$this->view['update_time'] = $update_time;
		$this->view['backup_dir'] =$this->model('upgrade')->backup_dir;
		$this->view['title'] = lang('Upgrade');
		$this->view('upgrade.html');
	}
	
	public function update()
	{
		if($this->check() == 0){
			$this->error('No need to update');
		}
		$dif = $this->model('upgrade')->dif_versions();
		
		foreach($dif as $version){
			$num = trim($version[1]);
			$v = trim($version[0]);
			$files = $this->model('upgrade')->get_files($v);
			if($files){
				//Check file permissions
				foreach($files as $f){
					if(!$this->model('upgrade')->check_permission($f)){
						$this->error('Insufficient permissions to update the file: '.$f);
						return;
					}
				}
				
				foreach($files as $f){
					//backup
					$this->model('upgrade')->backup_file($f);
					//update
					$this->model('upgrade')->update_file($v, $f);
				}
			}
			
			//database
			$sqls = $this->model('upgrade')->get_database($v);
			if($sqls){
				$this->model('upgrade')->update_database($sqls);
			}
			
			//update version file
			$this->model('upgrade')->update_version($v, $num);
			$this->model('upgrade')->local_version = $v;
		}
		
		if($this->model('upgrade')->error){
			echo '<h2>Upgrade Error:</h2>';
			foreach($this->model('upgrade')->error as $v){
				echo 'version: '.$v['version'].' File: '.$v['file'].':<br/>';
				echo '----Please visit '.$v['url'].' to get this file content<br/><br/>';
			}
			exit;
		}else{
			$this->ok('Upgrade success', url('index/index'));
		}
	}
	
	public function ajax()
	{
		header('Content-type: text/html; charset=utf-8');
		echo $this->check();
	}
	
	private function check()
	{
		return 0;
		$versions = $this->model('upgrade')->get_update();
		if(!$versions){
			return 0;
		}
		$last = end($versions);
		$arr = explode('-', $last);
		$last_update = trim($arr[0]);
		$last_version = trim($arr[1]);
		if($last_update > $this->model('upgrade')->local_version){
			return $last_version;
		}else{
			return 0;
		}
	}
}

?>