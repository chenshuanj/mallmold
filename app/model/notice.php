<?php


class notice extends model
{
	public function mail($email, $title, $content, $id=0)
	{
		$setting = &$this->model('common')->setting();
		$email_set = array(
			'host' => $setting['smtp_host'],
			'port' => $setting['smtp_port'],
			'user' => $setting['smtp_user'],
			'pswd' => $setting['smtp_pswd'],
		);
		$mail = array(
			'from' => $setting['smtp_email'],
			'to' => $email,
			'title' => $title,
			'content' => $content,
		);
		$res = $this->load('lib/stmp', $email_set)->sendmail($mail);
		
		if(!$id && !$res && $setting['email_log'] == 1){
			$status = $res ? 1 : 0;
			$error = $this->load('lib/stmp')->error;
			$data = array(
				'email' => $email,
				'title' => $title,
				'content' => $content,
				'time' => time(),
				'status' => $status,
				'error' => $res ? '' : ($error ? $error : 'Connection timeout'),
			);
			$this->db->table('email_log')->insert($data);
		}
		
		if($id > 0){
			if($res){
				$this->db->table('email_log')->where("id=$id")->update(array('status'=>1));
			}else{
				$error = $this->load('lib/stmp')->error;
				$data = array(
					'status' => 0,
					'error' => $error ? $error : 'Connection timeout',
				);
				$this->db->table('email_log')->where("id=$id")->update($data);
			}
		}
		
		return $res;
	}
	
	public function getmailtpl($name)
	{
		$lang = $this->model('dictionary')->current_lang;
		$data = $this->model('mdata')->table('email_template')->where("type='frontend' and name='$name'")->get();
		$data['path'] = $lang.'_'.$data['path'];
		$file = BASE_PATH .'/'.APP_NAME.'/template/default/notice/'.$data['path'];
		if(!file_exists($file)){
			$this->load('lib/dir')->checkdir($file);
			file_put_contents($file, $data['content']);
		}
		return $data;
	}
}
?>