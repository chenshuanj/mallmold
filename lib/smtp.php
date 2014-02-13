<?php
/*
*	@smtp.php
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

class smtp
{
	private $fp;
	public $error;
	public $host;
	public $port = 25;
	public $user;
	public $pswd;
	
	public function __construct($setting = array())
    {
		$this->set($setting);
	}
	
	public function set($setting)
    {
		$setting['host'] && $this->host = $setting['host'];
		$setting['port'] && $this->port = $setting['port'];
		$setting['user'] && $this->user = $setting['user'];
		$setting['pswd'] && $this->pswd = $setting['pswd'];
		return $this;
	}
	
	public function check($mail)
    {
		if(!$this->host || !$this->port || !$this->user || !$this->pswd){
			$this->error = 'unsetting';
			return -1;
		}
		if(!$mail['from'] || !$mail['to'] || !$mail['title'] || !$mail['content']){
			$this->error = 'unconfig';
			return 0;
		}
		return 1;
	}
	
	public function sendmail(array $mail)
    {
		!$mail['from'] && $mail['from'] = $this->user;
		$status = $this->check($mail);
		if($status != 1){
			return false;
		}
		//connect
		if($this->connect() != 220){
			return false;
		}
		if($this->cmd('EHLO '.$this->host) != 250){
			return false;
		}
		//auth
		if($this->cmd('AUTH LOGIN') != 334){
			return false;
		}
		if($this->cmd(base64_encode($this->user)) != 334){
			return false;
		}
		if($this->cmd(base64_encode($this->pswd)) != 235){
			return false;
		}
		//ready
		if($this->cmd('MAIL FROM:<'.$mail['from'].'>') != 250){
			return false;
		}
		$status = $this->cmd('RCPT TO:<'.$mail['to'].'>');
		if(!in_array($status, array(250, 251))){
			return false;
		}
		if($this->cmd('DATA') != 354){
			return false;
		}
		//send
		$title = str_replace("\r\n.\r\n", ".", $mail['title']);
		$content = str_replace("\r\n.\r\n", ".", $mail['content']);
		$body = '';
		$body .= "Return-Path: ".$mail['from']."\n";
		$body .= "From: ".$this->user."<".$mail['from'].">\n";
		$body .= "Reply-to: ".$this->user."<".$mail['from'].">\n";
		$body .= "To: <".$mail['to'].">\n";
		$body .= "Subject: =?UTF-8?B?".base64_encode($title)."?=\n";
		$body .= "Message-ID: <".base64_encode($this->user)."@".$this->host.">\n";
		$body .= "X-Priority: 3\n";
		$body .= "MIME-Version: 1.0\n";
		$body .= "Content-Type: text/html; charset=utf-8;\n";
		$body .= "\n";
		$body .= "$content";
		$body .= "\r\n.";
		if($this->cmd($body) != 250){
			return false;
		}
		$this->cmd('QUIT');
		$this->close();
		return true;
	}
	
	private function connect()
	{
		$this->fp = fsockopen($this->host, $this->port, $errno, $errstr, 10);
		if(!$this->fp){
			$this->error = $errno;
			return false;
		}
		stream_set_timeout($this->fp, 30);
		return $this->getstatus();
	}
	
	private function close()
	{
		@fclose($this->fp);
	}
	
	private function cmd($cmd)
	{
		fwrite($this->fp, "$cmd\r\n");
		return $this->getstatus();
	}
	
	private function getstatus()
	{
		$res = fread($this->fp, 1024);
		$this->error = $res;
		return intval(substr($res, 0, 3));
	}
}
?>