<?php


require Action('common');

class reportAction extends commonAction
{
	public function index()
	{
		$list = $this->db->table('error_report')->order('time desc')->getlist();
		foreach($list as $k=>$v){
			$list[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
		}
		
		$this->db->table('error_report')->update(array('status'=>1));
		
		$this->view['list'] = $list;
		$this->view['title'] = lang('sysreport');
		$this->view('report/index.html');
	}
	
	public function clear()
	{
		$this->db->table('error_report')->where('1=1')->delete();
		$this->ok('edit_success', url('report/index'));
	}
	
	public function pay()
	{
		$list = $this->db->table('payment_error')->order('time desc')->getlist();
		foreach($list as $k=>$v){
			$list[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
		}
		
		$this->view['list'] = $list;
		$this->view['title'] = lang('payreport');
		$this->view('report/pay.html');
	}
	
	public function clearpay()
	{
		$this->db->table('payment_error')->where('1=1')->delete();
		$this->ok('edit_success', url('report/pay'));
	}
}

?>