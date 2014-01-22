<?php


class filter
{
	public function is_email($str)
	{
		return filter_var($str, FILTER_VALIDATE_EMAIL);
	}
	
	public function is_username($str)
	{
		return preg_match("/^[0-9a-zA-Z'\s]+$/", stripslashes($str));
	}
	
	public function is_number($str)
	{
		return preg_match("/^[0-9]+$/", $str);
	}
	
	public function filter_keyword($str)
	{
		$find = array('_', '[', ']', '^', '%');
		$replace = array('\_', '\[', '\]', '\^', '\%');
		return str_replace($find, $replace, $str);
	}
	
	public function filter_html($str)
	{
		return htmlentities($str, ENT_NOQUOTES, 'UTF-8');
	}
}
?>