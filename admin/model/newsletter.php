<?php
/*
*	@newsletter.php
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

class newsletter extends model
{
	public function create_sn()
	{
		$count = 1;
		while($count > 0){
			$sn = md5(time().rand(1000, 9999));
			$count = $this->db->table('newsletter')->where("sn='$sn'")->count();
		}
		
		return $sn;
	}
	
	public function add_event($newsletter_id)
	{
		$n = $this->db->table('scheduled_event')->where("event='newsletter:send' and args='$newsletter_id'")->count();
		if($n < 1){
			$data = array(
				'event' => 'newsletter:send',
				'args' => $newsletter_id,
				'add_time' => date('Y-m-d H:i:s'),
				'run_status' => 0,
			);
			$this->db->table('scheduled_event')->insert($data);
		}
	}
}
?>