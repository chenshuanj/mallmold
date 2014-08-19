<?php
/*
*	@paylistAction.php
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

require Action('common');

class paylistAction extends commonAction
{
	public function index()
	{
		$where = '';
		$order_sn = trim($_POST['order_sn']);
		if($order_sn){
			$where = "order_sn like '%$order_sn%'";
		}
		
		$total = $this->db->table('payment_log')->where($where)->count();
		$this->pager($total);
		
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$list = $this->db->table('payment_log')->where($where)->limit($limit)->order('time desc')->getlist();
		foreach($list as $k=>$v){
			$list[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
		}
		
		$symbols = array();
		$currencies = &$this->model('common')->currencies();
		foreach($currencies as $v){
			$symbols[$v['code']] = $v['symbol'];
		}
		
		$this->view['symbols'] = $symbols;
		$this->view['list'] = $list;
		$this->view['title'] = lang('paylist');
		$this->view('paylist/list.html');
	}
}

?>