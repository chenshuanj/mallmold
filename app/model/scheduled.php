<?php
/*
*	@scheduled.php
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

class scheduled extends model
{
	public function run()
	{
		$list = $this->db->table('scheduled')->where('status=1')->getlist();
		foreach($list as $scheduled){
			if($this->is_runtime($scheduled)){
				$action = $scheduled['event_method'];
				$this->model($scheduled['event_model'])->$action();
				
				$scheduled_id = $scheduled['scheduled_id'];
				$this->db->table('scheduled')->where("scheduled_id=$scheduled_id")->update(array('last_runtime' => date('Y-m-d H:i:s')));
			}
		}
	}
	
	private function is_runtime(array $scheduled)
	{
		$last_runtime = strtotime($scheduled['last_runtime']);
		$time_type = $scheduled['time_type']; //1-every,2-when
		$time_unit = $scheduled['time_unit'];
		$time_number = $scheduled['time_number'];
		$now = time();
		$next_time = 0;
		switch($time_unit){
			case 1; //minute
				if($time_type == 1){
					$next_time = $last_runtime + $time_number*60;
				}else{
					$i = date('i', $now);
					if(($now - $last_runtime) > (3600 + $time_number*60)){
						$next_time = $now;
					}elseif($i == $time_number && ($now - $last_runtime)>600){
						$next_time = $now;
					}else{
						$next_time = $last_runtime + 3600;
					}
				}
			break;
			case 2; //hour
				if($time_type == 1){
					$next_time = $last_runtime + $time_number*3600;
				}else{
					$h = date('h', $now);
					if($h == $time_number && ($now - $last_runtime)>3600){
						$next_time = $now;
					}elseif(($now - $last_runtime) > 3600*24){
						$next_time = $now;
					}else{
						$next_time = $last_runtime + 3600*24;
					}
				}
			break;
			case 3; //day
				if($time_type == 1){
					$next_time = $last_runtime + $time_number*3600*24;
				}else{
					$d = date('d', $now);
					if($d == $time_number && ($now - $last_runtime)>3600*24){
						$next_time = $now;
					}else{
						$next_time = $last_runtime + 3600*24*30;
					}
				}
			break;
			case 4; //month
				if($time_type == 1){
					$next_time = $last_runtime + $time_number*3600*24*30;
				}else{
					$m = date('m', $now);
					if($m == $time_number && ($now - $last_runtime)>3600*24*30){
						$next_time = $now;
					}else{
						$next_time = $last_runtime + 3600*24*30*12;
					}
				}
			break;
			case 5; //week
				if($time_type == 1){
					$next_time = $last_runtime + $time_number*3600*24*7;
				}else{
					$w = date('w', $now);
					if($w == $time_number && ($now - $last_runtime)>3600*24*7){
						$next_time = $now;
					}else{
						$next_time = $last_runtime + 3600*24*30*7;
					}
				}
			break;
			default: //minute
				if($time_type == 1){
					$next_time = $last_runtime + $time_number*60;
				}else{
					$i = date('i', $now);
					if($i == $time_number && ($now - $last_runtime)>600){
						$next_time = $now;
					}else{
						$next_time = $last_runtime + 3600;
					}
				}
			break;
		}
		
		if(($next_time - $now) < 60){
			return true;
		}else{
			return false;
		}
	}
}
?>