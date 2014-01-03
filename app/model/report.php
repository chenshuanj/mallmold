<?php


class report extends model
{
	public function add($type, $message, $email=1)
    {
		$data = array(
			'type' => $type,
			'message' => $message,
			'uri' => $_SERVER['REQUEST_URI'],
			'time' => time(),
		);
		$id = $this->db->table('error_report')->insert($data);
		
		//email notice
		if($email == 1){
			$setting = &$this->model('common')->setting();
			if($setting['admin_error_notice']==1 && $setting['admin_error_notice_email']){
				$this->model('event')->add('report.email', $id);
			}
		}
	}
	
	public function get($id)
    {
		$report = $this->db->table('error_report')->where("id=$id")->get();
		$report['time'] = date('Y-m-d H:i:s', $report['time']);
		return $report;
	}
}
?>