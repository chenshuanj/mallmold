<?php


class helpdesk extends model
{
	public function department_list()
	{
		$list = $this->cache('department_list');
		if(!$list){
			$list = $this->model('mdata')->table('helpdesk_department')->order('sort_order asc')->getlist();
			if(!$list){
				return null;
			}
			$this->cache('department_list', $list);
		}
		return $list;
	}
	
	public function add($data)
	{
		$data['user_id'] = $_SESSION['user_id'] ? intval($_SESSION['user_id']) : 0;
		$data['language'] = cookie('lang');
		$data['time'] = time();
		
		$id = $this->db->table('helpdesk')->insert($data);
		if($id){
			$setting = &$this->model('common')->setting();
			if($setting['admin_helpdesk_notice'] == 1 && $setting['admin_helpdesk_notice_email']){
				$this->model('event')->add('helpdesk.post', $id);
			}
		}
		
		return $id;
	}
	
	public function email_tpl($id = 0)
	{
		$data = $this->db->table('helpdesk')->where("id=$id")->get();
		$time = date('Y-m-d H:i:s', $data['time']);
		return array(
			'title' => 'You have a new helpdesk message',
			'content' => "Helpdesk ID: #$id, Submit time: $time",
		);
	}
}
?>