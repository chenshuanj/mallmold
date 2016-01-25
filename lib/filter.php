<?php
/*
*	@filter.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

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