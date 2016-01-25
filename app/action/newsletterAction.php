<?php
/*
*	@newsletterAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

class newsletterAction extends action
{
	public function __construct()
    {
		parent::__construct();
		require(APP_PATH .'model/functions.php');
	}
	
	public function subscribe()
	{
		$email = trim($_POST['email']);
		if($email){
			$status = $this->model('newsletter')->subscribe($email);
			$this->view['status'] = $status;
			$this->view('newsletter/subscribe.html');
		}
	}
	
	public function unsubscribe()
	{
		$email = trim($_GET['email']);
		if($email){
			$status = $this->model('newsletter')->unsubscribe($email);
			$this->view['status'] = $status;
			$this->view('newsletter/unsubscribe.html');
		}
	}
	
	public function statistics()
	{
		//create a image
		header('Content-type:image/gif');
		echo base64_decode('R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
		
		$sn = trim($_GET['sn']);
		$email = trim($_GET['ue']);
		$this->model('newsletter')->statistics($sn, $email);
	}
}

?>