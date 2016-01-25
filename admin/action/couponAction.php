<?php
/*
*	@couponAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require Action('common');

class couponAction extends commonAction
{
	public function index()
	{
		$total = $this->db->table('coupon')->count();
		$this->pager($total);
		
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$list = $this->db->table('coupon')->limit($limit)->getlist();
		foreach($list as $k=>$v){
			$list[$k]['time'] = date('Y-m-d H:i:s', $v['createtime']);
		}
		
		$this->view['list'] = $list;
		$this->view['title'] = lang('coupon');
		$this->view('coupon/index.html');
	}
	
	public function edit()
	{
		if(!$_POST['submit']){
			$id = intval($_GET['id']);
			if($id){
				$data = $this->db->table('coupon')->where("id=$id")->get();
				$data['expiretime'] && $data['expiretime'] = date('Y-m-d H:i:s', $data['expiretime']);
				$this->view['data'] = $data;
			}
			$this->view('coupon/edit.html');
		}else{
			if(!$_POST['sendto'] || !$_POST['money']){
				$this->error('required_null');
			}
			
			if(!is_email(trim($_POST['sendto']))){
				$this->error('emailfmt_error');
			}
			
			$code = $_POST['code'] ? trim($_POST['code']) : $this->makecode();
			$expiretime = $_POST['expiretime'] ? strtotime($_POST['expiretime']) : 0;
			
			$data = array(
				'code' => $code,
				'money' => floatval($_POST['money']),
				'sendto' => trim($_POST['sendto']),
				'expiretime' => $expiretime,
				'createtime' => time(),
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->db->table('coupon')->where("id=$id")->update($data);
			}else{
				$id = $this->db->table('coupon')->insert($data);
			}
			
			if($_POST['send'] == 1){
				$this->send($id);
			}
			
			$this->ok('edit_success', url('coupon/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$this->db->table('coupon')->where("id=$id")->delete();
			$this->ok('delete_done', url('coupon/index'));
		}else{
			$this->error('args_error');
		}
	}
	
	private function send($id)
	{
		$data = $this->db->table('coupon')->where("id=$id")->get();
		$this->view['coupon'] = $data;
		
		$send_lang = $_POST['send_lang'] ? trim($_POST['send_lang']) : null;
		$mail = $this->model('notice')->getmailtpl('send_coupon', $send_lang);
		if(!$mail){
			$this->error('cannot_find_emailtpl');
			return false;
		}
		
		$content = $this->view('notice/'.$mail['path'], 0);
		$res = $this->model('notice')->mail($data['sendto'], $mail['title'], $content);
		if($res){
			$this->db->table('coupon')->where("id=$id")->update(array('send'=>1));
		}
		return $res;
	}
	
	private function makecode()
	{
		$str = 'abcdefghijkmnopqrstuvwsyzABCDEFGHIJKMNOPQRSTUVWSYZ0123456789';
		$n = strlen($str);
		$code = '';
		for($i=0; $i<12; $i++){
			$k = rand(0, $n-1);
			$code .= $str[$k];
		}
		
		$n = $this->db->table('coupon')->where("code='$code'")->count();
		if($n > 0){
			return $this->makecode();
		}else{
			return $code;
		}
	}
}

?>