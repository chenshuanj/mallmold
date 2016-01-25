<?php
/*
*	@login.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

class login extends model
{
	public function checklogin()
	{
		if(!$_SESSION['m_id'] || !$_SESSION['m_key']){
			return false;
		}
		$m_id = $_SESSION['m_id'];
		$m_key = $_SESSION['m_key'];
		
		$rs = $this->db->table('admin')->where("id='$m_id' and status=1")->get();
		if(!$rs || $m_key != md5($rs['id'].'|'.md5($rs['pswd']))){
			return false;
		}else{
			return true;
		}
	}
	
	public function dologin($username, $password)
	{
		if(!$username || !$password){
			return false;
		}
		
		$rs = $this->db->table('admin')->where("name='$username' and status=1")->get();
		if($rs && $this->encrypt($password, $rs['salt'])==$rs['pswd']){
			$_SESSION['m_id'] = $rs['id'];
			$_SESSION['m_key'] = md5($rs['id'].'|'.md5($rs['pswd']));
			return true;
		}else{
			return false;
		}
	}
	
	public function logout()
	{
		$_SESSION['m_id'] = '';
		$_SESSION['m_key'] = '';
		session_destroy();
	}
	
	public function encrypt($password, $salt)
	{
		return md5(md5($password).$salt);
	}
	
	public function create_salt()
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