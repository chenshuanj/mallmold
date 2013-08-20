<?php
/*
*	@commonfunc.php
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

function cookie($name, $value=null, $domain='')
{
	if($name && isset($value)){
		$_COOKIE[$name] = $value;
		return setcookie($name, $value, time()+86400, '/');
	}elseif($name){
		return $_COOKIE[$name];
	}else{
		return false;
	}
}

function cmp_func($a, $b){
	if ($a['is_dir'] && !$b['is_dir']) {
		return -1;
	} else if (!$a['is_dir'] && $b['is_dir']) {
		return 1;
	} else {
		if ($GLOBALS['upload_file_order'] == 'size') {
			if ($a['filesize'] > $b['filesize']) {
				return 1;
			} else if ($a['filesize'] < $b['filesize']) {
				return -1;
			} else {
				return 0;
			}
		} else if ($GLOBALS['upload_file_order'] == 'type') {
			return strcmp($a['filetype'], $b['filetype']);
		} else {
			return strcmp($a['filename'], $b['filename']);
		}
	}
}

function to_url($str){
	$url = preg_replace("/\W/", '-', stripslashes($str));
	return strtolower(trim(preg_replace("/[\-]{2,}/", '-', $url), '-'));
}

function is_email($str){
	return filter_var($str, FILTER_VALIDATE_EMAIL);
}
?>