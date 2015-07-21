<?php
/*
*	@core.php
*	Copyright (c)2013-2014 Mallmold Ecommerce(HK) Limited.
*	
*	This library is free software; you can redistribute it and/or
*	modify it under the terms of the GNU Lesser General Public
*	License as published by the Free Software Foundation; either
*	version 2.1 of the License, or (at your option) any later version.

*	This library is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
*	GNU Lesser General Public License for more details.
*/

function addslashes_deep($var)
{
	if(is_array($var)){
		foreach($var as $k=>$v){
			$var[$k] = addslashes_deep($v);
		}
		return $var;
	}else{
		return addslashes($var);
	}
}

function error($msg = null)
{
	if($msg instanceof Exception){
		$error_msg = $msg->getMessage();
		$errors = $msg->getTrace();
	}else{
		$error_msg = $msg;
		$errors = debug_backtrace();
	}
	
	if($error_msg){
		echo "<h2>$error_msg</h2>";
	}
	
	foreach($errors as $num=>$error){
		$args = $error['args'] ? implode(', ', $error['args']) : '';
		echo "#$num  [".str_replace(BASE_PATH, '', str_replace('\\', '/', $error['file'])).':'.$error['line'].'] ';
		echo ($error['class'] ? $error['class'].'->' : '').($error['function'] ? $error['function']."($args)" : '');
		echo '<br/>';
	}
	
	exit;
}

set_error_handler('error', E_ERROR);
set_exception_handler('error');

function error_404($msg)
{
	$script = APP_PATH .'action/errorAction.php';
	if(file_exists($script)){
		require($script);
		$run = new errorAction();
		if(is_callable(array($run, '__404'))){
			$run->__404($msg);
		}else{
			header("HTTP/1.1 404 Not Found");
			echo $msg;
			exit;
		}
	}else{
		header("HTTP/1.1 404 Not Found");
		echo $msg;
		exit;
	}
}

function loadclass($class, $path, $args=null)
{
	static $_classes = array();
	
	if(isset($_classes[$class])){
		return $_classes[$class];
	}else{
		$script = ($path ? '/'.$path.'/' : '').$class.'.php';
		if(!file_exists(BASE_PATH .$script)){
			error('Can not find the file '. BASE_PATH .$script);
		}else{
			require(BASE_PATH .$script);
			if(class_exists($class) === FALSE){
				error('Can not find the class '.$class.' in the file '.$script);
			}else{
				if($args){
					$_classes[$class] = new $class($args);
				}else{
					$_classes[$class] = new $class();
				}
				return $_classes[$class];
			}
		}
	}
}

function Action($name)
{
	$script = APP_PATH .'action/'.$name.'Action.php';
	if(!file_exists($script)){
		error('Can not find the action '.$name.'Action.php');
	}else{
		return $script;
	}
}

function lang($word)
{
	static $lang = array();
	
	$config = &$GLOBALS['config'];
	if(!$lang && $config['LAN_NAME']){
		$lang_path = APP_PATH .'language/'.$config['LAN_NAME'].'.php';
		if(!file_exists($lang_path)){
			error('Can not find the file '. $lang_path);
		}else{
			$lang = include $lang_path;
		}
	}
	
	if($lang[$word]){
		return $lang[$word];
	}else{
		$args = explode(',', $word);
		$format = trim($args[0]);
		if($lang[$format]){
			unset($args[0]);
			foreach($args as $k=>$v){
				$args[$k] = trim($v);
			}
			return vsprintf($lang[$format], $args);
		}else{
			return $word;
		}
	}
}

function router($router)
{
	if(isset($router['type']) && $router['type'] > 0){
		$path = preg_replace("'^".PHP_PATH."'", '', $_SERVER['REQUEST_URI']);
		$path = preg_replace("'^".PHP_NAME."'", '', $path);
		if($path){
			if(strpos($path, '?') !== false){
				$uri = explode('?', $path);
				$path = $uri[0];
				if($uri[1]){
					parse_str($uri[1], $para);
					$_GET = array_merge($_GET, $para);
				}
			}
			if($path){
				if($path[0] == '/'){
					$path = substr($path, 1);
				}
				$request = explode('/', $path);
				$_GET['c'] = $request[0];
				$_GET['a'] = $request[1];
				for($i=2; $i<count($request); $i++){
					$_GET[$request[$i]] = $request[++$i];
				}
			}
		}
	}
	
	$module = !empty($_GET['c']) ? trim($_GET['c']) : 'index';
	$action = !empty($_GET['a']) ? trim($_GET['a']) : 'index';
	$rule = array();
	if(isset($router['*/*'])){
		$rule = $router['*/*'];
	}
	if(isset($router["$module/*"])){
		$rule = array_merge($rule, $router["$module/*"]);
	}
	if(isset($router["$module/$action"])){
		$rule = array_merge($rule, $router["$module/$action"]);
	}
	
	if(!empty($rule['scheme'])){
		if(isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == 1)){
			$scheme = 'https';
		}else{
			$scheme = 'http';
		}
		
		if($scheme != $rule['scheme']){
			$host = !empty($rule['host']) ? $rule['host'] : $_SERVER['HTTP_HOST'];
			$location = $rule['scheme'].'://'.$host.$_SERVER['REQUEST_URI'];
			header('Location: '.$location);
			exit;
		}
	}
	if(!empty($rule['rewrite'])){
		$method = explode('/', $rule['rewrite']);
		$module = !empty($method[0]) ? $method[0] : 'index';
		$action = !empty($method[1]) ? $method[1] : 'index';
	}
	if(!empty($rule['query'])){
		parse_str($rule['query'], $para);
		$_GET = array_merge($_GET, $para);
	}
	
	return array(
		'module' => $module,
		'action' => $action,
	);
}

function url($url)
{
	$router = &$GLOBALS['router'];
	$uri = explode('?', $url);
	$ma = explode('/', $uri[0]);
	$type = isset($router['type']) ? $router['type'] : 0;
	
	$rule = isset($router['*/*']) ? $router['*/*'] : array();
	$rule = isset($router[$ma[0].'/*']) ? array_merge($rule, $router[$ma[0].'/*']) : $rule;
	$rule = isset($router[$uri[0]]) ? array_merge($rule, $router[$uri[0]]) : $rule;
	
	$url = (!empty($rule['scheme']) ? $rule['scheme'] : 'http').'://';
	$url .= !empty($rule['host']) ? $rule['host'] : $_SERVER['HTTP_HOST'];
	
	if($type == 0){
		$url .= $_SERVER['SCRIPT_NAME'].'?c='.$ma[0].'&a='.$ma[1].($uri[1] ? '&'.$uri[1] : '');
	}else{
		$url .= ($type == 1 ? $_SERVER['SCRIPT_NAME'] : '').'/';
		$url .= $uri[0];
		$query = explode('&', $uri[1]);
		foreach($query as $q){
			if($q){
				$para = explode('=', $q, 2);
				$url .= '/'.$para[0].'/'.$para[1];
			}
		}
	}
	
	return $url;
}

class core
{
	protected $db;
	protected $config;
	
	public function __construct()
    {
		$this->db = &$GLOBALS['db'];
		$this->config = &$GLOBALS['config'];
	}
	
	public function model($name)
    {
		return loadclass($name, APP_NAME.'/model');
	}
	
	public function load($file, $args=null)
    {
		$node = explode('/', $file);
		$name = end($node);
		$n = count($node) - 1;
		unset($node[$n]);
		$path = implode('/', $node);
		return loadclass($name, $path, $args);
	}
	
	public function cache($key, $data=null, $check_lang=1)
	{
		if($this->config['DATA_CACHE'] === false || !$key){
			return null;
		}
		$check_lang==1 && $key = $this->config['LAN_NAME'].'_'.$key;
		$cache_path = APP_PATH .'cache/data';
		!is_dir($cache_path) && mkdir($cache_path, 0777, true);
		$file = $cache_path.'/'.md5($key).'.php';
		if($data == null){
			if(file_exists($file)){
				$timeout = $this->config['CACHE_TIME'] ? $this->config['CACHE_TIME'] : 3600;
				if((time() - filemtime($file)) > $timeout){
					return false;
				}else{
					$data = include $file;
					return $data;
				}
			}else{
				return false;
			}
		}else{
			return file_put_contents($file, '<?php return '.var_export($data, true).'; ?>');
		}
	}
}

class action extends core
{
	protected $tpl;
	protected $tpl_name = 'default';
	protected $view = array();
	
	public function __construct()
    {
		parent::__construct();
		
		if($this->config['TPL_NAME']){
			$this->set_template($this->config['TPL_NAME']);
		}
	}
	
	public function set_template($name)
    {
		if(!$name){
			return false;
		}
		
		$this->tpl_name = $name;
	}
	
	protected function view($path='', $output=1)
    {
		if(!$this->tpl){
			require(CORE_PATH .'/template.php');
			
			$this->tpl = new template();
			$this->tpl->tpl_dir = APP_PATH .'template/';
			$this->tpl->tpl_default = 'default';
			$this->tpl->tpl_name = $this->tpl_name;
			$this->tpl->tpl_path = PHP_PATH.APP_NAME.'/template/'.$this->tpl_name.'/';
			$this->tpl->cache_path = APP_PATH .'cache/template/';
			$this->config['TPL_CACHE'] && $this->tpl->cache = 1;
		}
		
		!$path && $path = MODULE .'/'. ACTION.'.html';
		$tpl_file = $this->tpl->build($path, 0);
		$this->view && extract($this->view);
		
		if($output == 1){
			header("Content-Type:text/html;charset=utf-8");
			require($tpl_file);
			return;
		}else{
			ob_start();
			require($tpl_file);
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
	}
}

class model extends core
{
	public function __construct()
    {
		parent::__construct();
	}
}
?>