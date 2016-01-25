<?php
/*
*	@newsletter.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

class newsletter extends model
{
	public function get_detail($newsletter_id, $lang)
	{
		static $details = array();
		if(!isset($details[$newsletter_id][$lang])){
			$details[$newsletter_id][$lang] = $this->model('mdata')
													->table('newsletter')
													->field('title_key_,content_txtkey_')
													->where("newsletter_id=$newsletter_id")
													->get($lang);
		}
		
		return $details[$newsletter_id][$lang];
	}
	
	public function unsubscribe_html($email)
	{
		return '<a href="'.url('newsletter/unsubscribe?email='.$email).'" target="_blank">Unsubscribe</a>';
	}
	
	public function send($newsletter_id)
	{
		$newsletter = $this->db->table('newsletter')->where("newsletter_id=$newsletter_id")->get();
		if($newsletter['enable'] == 1 && $newsletter['status'] < 2){
			$this->db->table('newsletter')->where("newsletter_id=$newsletter_id")->update('status=1');
			
			$where = "subscriber_id not in (
						".$this->db->table('newsletter_send')->field('subscriber_id')->where("newsletter_id=$newsletter_id")->getsql()."
					) and status=1";
			$subscribers = $this->db->table('newsletter_subscriber')->field('subscriber_id,email,language')->where($where)->getlist();
			$num = 0;
			foreach($subscribers as $subscriber){
				$detail = $this->get_detail($newsletter_id, $subscriber['language']);
				$statistics_url = 'newsletter/statistics?sn='.$newsletter['sn'].'&ue='.$subscriber['email'];
				$detail['content'] .= '<br/><img src="'.url($statistics_url).'" width="1" height="1">';
				$detail['content'] .= '<br/>'.$this->unsubscribe_html($subscriber['email']);
				$res = $this->model('notice')->mail($subscriber['email'], $detail['title'], $detail['content']);
				
				$data = array(
					'newsletter_id' => $newsletter_id,
					'subscriber_id' => $subscriber['subscriber_id'],
					'send_status' => ($res ? 1 : 0)
				);
				$this->db->table('newsletter_send')->insert($data);
				
				if($res){
					$num++;
					$this->db->table('newsletter')->where("newsletter_id=$newsletter_id")->addnum('sent', 1);
					$this->db->table('newsletter_subscriber')->where("subscriber_id=".$subscriber['subscriber_id'])->addnum('total_send', 1);
				}
			}
		}
		
		$this->db->table('newsletter')->where("newsletter_id=$newsletter_id")->update('status=2');
		$this->db->table('scheduled_event')->where("event='newsletter:send' and args='$newsletter_id'")->update('run_status=1');
	}
	
	public function subscribe($email)
	{
		if(!$this->load('lib/filter')->is_email($email)){
			return false;
		}
		
		$subscriber_id = $this->db->table('newsletter_subscriber')->where("email='$email'")->getval('subscriber_id');
		if(!$subscriber_id){
			$data = array(
				'email' => $email,
				'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0,
				'language' => cookie('lang'),
				'addtime' => time(),
				'status' => 1
			);
			return $this->db->table('newsletter_subscriber')->insert($data);
		}else{
			return $this->db->table('newsletter_subscriber')->where("email='$email'")->update('status=1');
		}
	}
	
	public function unsubscribe($email)
	{
		if(!$this->load('lib/filter')->is_email($email)){
			return false;
		}
		
		return $this->db->table('newsletter_subscriber')->where("email='$email'")->update('status=0');
	}
	
	public function statistics($sn, $email)
	{
		$newsletter_id = $this->db->table('newsletter')->where("sn='$sn'")->getval('newsletter_id');
		if(!$newsletter_id){
			return false;
		}
		
		$subscriber_id = $this->db->table('newsletter_subscriber')->where("email='$email'")->getval('subscriber_id');
		if(!$subscriber_id){
			return false;
		}
		
		$where = "newsletter_id=$newsletter_id and subscriber_id=$subscriber_id";
		$read_status = $this->db->table('newsletter_send')->where($where)->getval('read_status');
		if($read_status != 1){
			$this->db->table('newsletter_send')->where($where)->update('read_status=1');
			$this->db->table('newsletter_subscriber')->where("subscriber_id=$subscriber_id")->addnum('total_read', 1);
		}
	}
}
?>