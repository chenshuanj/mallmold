<?php
/*
*	@helpdeskAction.php
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

class helpdeskAction extends commonAction
{
	public function form()
	{
		if($this->model('user')->is_login()){
			$user = $this->model('user')->get();
			$this->view['firstname'] = $user['firstname'];
			$this->view['lastname'] = $user['lastname'];
			$this->view['email'] = $user['email'];
		}
		
		$this->view['departments'] = $this->model('helpdesk')->department_list();
		
		$map[] = array('title'=>lang('Contact Us'));
		$this->view['map'] = $map;
		$this->view['html_title'] = lang('Contact Us');
		$this->view('helpdesk/form.html');
	}
	
	public function post()
	{
		$firstname = trim($_POST['firstname']);
		$lastname = trim($_POST['lastname']);
		$email = trim($_POST['email']);
		$phone = trim($_POST['phone']);
		$department_id = intval($_POST['department_id']);
		$priority = intval($_POST['priority']);
		$title = trim($_POST['title']);
		$message = trim($_POST['message']);
		
		if(!$firstname || !$firstname || !$email || !$title || !$message){
			$this->error('Please fill in the required fields');
			return;
		}
		
		$data = array(
			'department_id' => $department_id,
			'email' => $email,
			'firstname' => $firstname,
			'lastname' => $lastname,
			'phone' => $phone,
			'priority' => $priority,
			'title' => $title,
			'message' => $message,
		);
		
		$rs = $this->model('helpdesk')->add($data);
		if($rs){
			$this->ok('Your form has been submitted, we will contact you soon!');
			return;
		}else{
			$this->error('Submit failed');
			return;
		}
	}
}

?>