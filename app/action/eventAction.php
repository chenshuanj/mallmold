<?php
/*
*	@eventAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

class eventAction extends action
{
	public function __construct()
    {
		parent::__construct();
		
		ignore_user_abort(true);
		set_time_limit(0);
		require(APP_PATH .'model/functions.php');
	}
	
	public function index()
	{
		$do = trim($_GET['do']);
		$e = $this->model('event')->get($do);
		if(!$e){
			return null;
		}
		
		$lang = trim($_GET['lang']);
		cookie('lang', $lang);
		
		$setting = &$this->model('common')->setting();
		switch($e['action']){
			case 'report.email': //error report for manager
				$id = intval($e['id']);
				$report = $this->model('report')->get($id);
				if($report){
					$mail = $this->model('notice')->getmailtpl('error_report');
					$this->view['report'] = $report;
					$content = $this->view('notice/'.$mail['path'], 0);
					$emails = explode(',', $setting['admin_error_notice_email']);
					foreach($emails as $email){
						$email = trim($email);
						if($email){
							$this->model('notice')->mail($email, $mail['title'], $content);
						}
					}
				}
				break;
			case 'user.register': //customer register
				$user_id = intval($e['id']);
				if($user_id > 0){
					$user = $this->model('user')->get($user_id);
					if(!$user){
						return null;
					}
					$mail = $this->model('notice')->getmailtpl('new_customer');
					$this->view['user'] = $user;
					$content = $this->view('notice/'.$mail['path'], 0);
					$this->model('notice')->mail($user['email'], $mail['title'], $content);
				}
				break;
			case 'order.creat': //for customer when order is created
				$order_id = intval($e['id']);
				if($order_id > 0){
					$order = $this->model('order')->order_get($order_id);
					if(!$order){
						return null;
					}
					$mail = $this->model('notice')->getmailtpl('new_order');
					$this->view['order'] = $order;
					$content = $this->view('notice/'.$mail['path'], 0);
					$this->model('notice')->mail($order['email'], $mail['title'], $content);
				}
				break;
			case 'order.pay': //for customer when order is payed
				$order_id = intval($e['id']);
				if($order_id > 0){
					$order = $this->model('order')->order_get($order_id);
					if(!$order){
						return null;
					}
					$mail = $this->model('notice')->getmailtpl('order_pay');
					$this->view['order'] = $order;
					$content = $this->view('notice/'.$mail['path'], 0);
					$this->model('notice')->mail($order['email'], $mail['title'], $content);
				}
				break;
			case 'order.notice': //for manager when order is payed
				$order_id = intval($e['id']);
				if($order_id > 0){
					$order = $this->model('order')->order_get($order_id);
					if(!$order){
						return null;
					}
					$mail = $this->model('notice')->getmailtpl('new_order_admin');
					$this->view['order'] = $order;
					$content = $this->view('notice/'.$mail['path'], 0);
					$emails = explode(',', $setting['admin_order_notice_email']);
					foreach($emails as $email){
						$email = trim($email);
						if($email){
							$this->model('notice')->mail($email, $mail['title'], $content);
						}
					}
				}
				break;
			case 'coupon.send': //send coupon gift
				$gift_id = intval($e['id']);
				if($gift_id > 0){
					$coupon = $this->db->table('coupon')->where("id=$gift_id")->get();
					if(!$coupon || $coupon['status'] != 0){
						return null;
					}
					$this->view['coupon'] = $coupon;
					$mail = $this->model('notice')->getmailtpl('send_coupon');
					$content = $this->view('notice/'.$mail['path'], 0);
					$res = $this->model('notice')->mail($coupon['email'], $mail['title'], $content);
					if($res){
						$this->db->table('coupon')->where("id=$gift_id")->update(array('send'=>1));
					}
				}
				break;
			case 'helpdesk.post':
				$id = intval($e['id']);
				if($id > 0){
					$mail = $this->model('helpdesk')->email_tpl($id);
					$emails = explode(',', $setting['admin_helpdesk_notice_email']);
					foreach($emails as $email){
						$email = trim($email);
						if($email){
							$this->model('notice')->mail($email, $mail['title'], $mail['content']);
						}
					}
				}
				break;
			case 'backend': //backend task
				$id = trim($e['id']);
				if($id == 'sitemap.generate'){
					$this->model('sitemap')->generate();
				}
				break;
			default:
				break;
		}
		$this->model('cart')->clear();
		$this->model('checkout')->clear_checkout();
		return null;
	}
	
	public function scheduled()
	{
		$this->model('scheduled')->run();
		return null;
	}
}

?>