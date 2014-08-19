<?php
/*
*	@report.php
*	Copyright (c)2013-2014 Mallmold Ecommerce(HK) Limited. 
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

class report extends model
{
	public function add($type, $message, $email=1)
    {
		$data = array(
			'type' => $type,
			'message' => $message,
			'uri' => $_SERVER['REQUEST_URI'],
			'time' => time(),
		);
		$id = $this->db->table('error_report')->insert($data);
		
		//email notice
		if($email == 1){
			$setting = &$this->model('common')->setting();
			if($setting['admin_error_notice']==1 && $setting['admin_error_notice_email']){
				$this->model('event')->add('report.email', $id);
			}
		}
	}
	
	public function get($id)
    {
		$report = $this->db->table('error_report')->where("id=$id")->get();
		$report['time'] = date('Y-m-d H:i:s', $report['time']);
		return $report;
	}
}
?>