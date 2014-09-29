<?php
/*
*	@run.php
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

if(!defined('APP_NAME'))
{
	exit('Undefined APP_NAME');
}

define('APP_PATH', BASE_PATH .'/'. APP_NAME . '/');

$config = include APP_PATH .'config.php';

if($config['SHOW_ERROR'] == 1)
{
	@ini_set('display_errors', 1);
	error_reporting(E_ALL ^ E_NOTICE);
}
else
{
	@ini_set('display_errors', 0);
	error_reporting(0);
}

if(isset($config['TIME_LIMIT']))
{
	set_time_limit($config['TIME_LIMIT']);
}

if(isset($config['TIMEZONE']))
{
	date_default_timezone_set($config['TIMEZONE']);
}

define('PHP_NAME', substr(strrchr($_SERVER['SCRIPT_NAME'], '/'), 1));
define('PHP_PATH', str_replace(PHP_NAME, '', $_SERVER['SCRIPT_NAME']));
define('CORE_PATH', str_replace('\\','/',dirname(__FILE__)));
require(CORE_PATH .'/core.php');

if(!get_magic_quotes_gpc())
{
	!empty($_GET) && $_GET = addslashes_deep($_GET);
	!empty($_POST) && $_POST = addslashes_deep($_POST);
	!empty($_COOKIE) && $_COOKIE = addslashes_deep($_COOKIE);
}

if(isset($_REQUEST['session_id']))
{
	session_id(trim($_REQUEST['session_id']));
}
session_start();

$router = array();
if(file_exists(APP_PATH .'router.php'))
{
	$router = include APP_PATH .'router.php';
}
$_uri = router($router);

define('MODULE', $_uri['module']);
define('ACTION', $_uri['action']);

require(CORE_PATH .'/db.php');
$db = new db();
if(isset($config['DB_DRIVER']))
{
	$db->set_driver($config['DB_DRIVER']);
}
if(isset($config['DB_PREFIX']))
{
	$db->prefix($config['DB_PREFIX']);
}
$db_host = $config['DB_HOST'].(isset($config['DB_PORT']) ? ':'.$config['DB_PORT'] : '');
$db->setting($db_host, $config['DB_USER'], $config['DB_PSWD'], $config['DB_NAME']);

$script = APP_PATH .'action/'.MODULE.'Action.php';
if(!file_exists($script))
{
	error_404('Can not find the action '.MODULE.'Action.php');
}
else
{
	require($script);
	$class = MODULE.'Action';
	$method = ACTION;
	if($method != $class)
	{
		$run = new $class();
		if(method_exists($run, $method)){
			$run->$method();
		}else{
			error_404("Call to undefined method($method) in the class($class)");
		}
	}
	else
	{
		error("The action name($method) must be not the same as the module name($class)");
	}
}

if($db->conn)
{
	$db->close();
}
?>