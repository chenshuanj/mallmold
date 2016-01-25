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

class indexAction extends action
{
	public function index()
	{
		$this->view('index.html');
	}
	
	public function setting()
	{
		$this->view('setting.html');
	}
	
	public function install()
	{
		$host = $_POST['host'] ? trim($_POST['host']) : '127.0.0.1';
		$port = $_POST['port'] ? $_POST['port'] : 3306;
		$dbname = trim($_POST['dbname']);
		$prefix = trim($_POST['prefix']);
		$user = trim($_POST['user']);
		$pswd = $_POST['pswd'];
		$admin = trim($_POST['admin']);
		$password = $_POST['password'];
		
		if(!$dbname || !$user || !$pswd || !$admin || !$password){
			echo 'Please enter the required fields';
			echo '<br/><input type="button" onclick="history.go(-1);" value="Back">';
			return;
		}
		
		//mysql or pdo_mysql
		if(extension_loaded('pdo_mysql')){
			$db_driver = 'pdo_mysql';
		}elseif(extension_loaded('mysqli')){
			$db_driver = 'mysqli';
		}else{
			$db_driver = 'mysql';
		}
		
		$this->db->set_driver($db_driver);
		$this->db->setting("$host:$port", $user, $pswd, $dbname);
		
		$config = array(
			//base
			'SHOW_ERROR' => 1,
			'TIME_LIMIT' => 0,
			'TIMEZONE' => 'UTC',
			
			//database 
			'DB_HOST' => $host,
			'DB_PORT' => $port,
			'DB_NAME' => $dbname,
			'DB_USER' => $user,
			'DB_PSWD' => $pswd,
			'DB_PREFIX' => $prefix,
			'DB_DRIVER' => $db_driver,
			
			//control
			'TPL_NAME' => 'default',
			'TPL_CACHE' => true,
			'DATA_CACHE' => true,
			'CACHE_TIME' => 3600,
			'LAN_NAME' => 'en',
		);
		
		$str = $str = "<?php\r\nreturn ".var_export($config, true)."\r\n?>";
		$str = preg_replace('/=>\s+\n\s+/s', '=> ', $str);
		$str = preg_replace('/\n[\s]{2,2}/', "\n\t", $str);
		$str = preg_replace('/\n\t[\s]{2,2}/', "\n\t\t", $str);
		
		$file = BASE_PATH .'/app/config.php';
		file_put_contents($file, $str);
		$file = BASE_PATH .'/admin/config.php';
		file_put_contents($file, $str);
		
		$file = BASE_PATH .'/'. APP_NAME .'/database.sql';
		$fp = fopen($file, 'r');
		$sqls = fread($fp, filesize($file));
		fclose($fp);
		
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
				$sql = str_replace('{PREFIX}', $prefix, $sql);
				$this->db->query($sql);
				$sql = '';
			}
		}
		
		//admin account
		$salt = $this->create_salt();
		$pswd = $this->encrypt($password, $salt);
		$this->db->query("INSERT INTO `".$prefix."admin` (`id`, `group_id`, `name`, `pswd`, `salt`, `status`) 
							VALUES
						 (1, 0, '$admin', '$pswd', '$salt', 1)");
		
		//rename file
		rename('index.php', 'install.php.default');
		rename('index.php.default', 'index.php');
		rename('admin.php.default', 'admin.php');
		
		$this->view('install.html');
	}
	
	private function encrypt($password, $salt)
	{
		return md5(md5($password).$salt);
	}
	
	private function create_salt()
	{
		$str = 'abcdefghijkmnopqrstuvwsyz';
		$str .= strtoupper($str);
		$n = strlen($str);
		$k1 = rand(0, $n-1);
		$k2 = rand(0, $n-1);
		return $str[$k1].$str[$k2];
	}
}

?>