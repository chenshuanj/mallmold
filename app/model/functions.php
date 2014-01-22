<?php


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
?>