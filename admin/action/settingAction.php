<?php
/*
*	@settingAction.php
*	Copyright (c)2013 Mallmold Ecommerce(HK) Limited. 
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

class settingAction extends commonAction
{
	public function index()
	{
		$data = $this->getconfig();
		
		$this->editor_header();
		$this->editor_uploadbutton('web_logo', $data['web_logo'], 'other');
		$this->editor_uploadbutton('btm_logo', $data['btm_logo'], 'other');
		
		$this->view['country'] = $this->db->table('country')->where('status=1')->getlist();
		$this->view['language'] = &$this->model('common')->languages();
		$this->view['currency'] = &$this->model('common')->currencies();
		
		$image_setting = array();
		$image_types = $this->model('image')->gettypes();
		foreach($image_types as $k=>$v){
			$image_setting[$k] = $this->mdata('image_setting')->where("type='$k' and status=1")->getlist();
		}
		$this->view['image_setting'] = $image_setting;
		$this->view['grouplist'] = $this->mdata('user_group')->where('status=1')->getlist();
		$this->view['data'] = $data;
		$this->view['title'] = lang('setting');
		$this->view('setting/index.html');
	}
	
	public function update()
	{
		$data = $this->getconfig();
		
		$sys = $_POST['sys'];
		$sys = $this->model('dict')->setdict($sys);
		
		$sys['web_logo'] = $_POST['web_logo'];
		$sys['btm_logo'] = $_POST['btm_logo'];
		
		foreach($sys as $k=>$v){
			if(isset($data[$k])){
				if($data[$k] != $v){
					$this->db->table('setting')->where("name='$k'")->update(array('val'=>$v));
				}
			}else{
				$this->db->table('setting')->insert(array('name'=>$k, 'val'=>$v));
			}
		}
		
		$this->ok('edit_success', url('setting/index'));
	}
	
	private function getconfig(){
		$data = array();
		$list = $this->db->table('setting')->getlist();
		foreach($list as $v){
			$data[$v['name']] = $v['val'];
		}
		return $this->model('dict')->getdict($data);
	}
}

?>