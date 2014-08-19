<?php
/*
*	@imagesettingAction.php
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

class imagesettingAction extends commonAction
{
	public function index()
	{
		$this->view['list'] = $this->mdata('image_setting')->getlist();
		$this->view['types'] = $this->model('image')->gettypes();
		$this->view['title'] = lang('imgscheme');
		$this->view('image_setting/list.html');
	}
	
	public function add()
	{
		if(!$_POST['submit']){
			if($_GET['id']){
				$date = $this->mdata('image_setting')->where("id=".intval($_GET['id']))->get();
			}else{
				$date = array();
			}
			
			$this->editor_header();
			$this->editor_uploadbutton('watermark_img', $date['watermark_img'], 'other');
			
			$this->view['data'] = $date;
			$this->view['types'] = $this->model('image')->gettypes();
			$this->view['title'] = lang('edit_scheme');
			$this->view('image_setting/add.html');
		}else{
			if(!$_POST['name'] || !$_POST['sign'] || !$_POST['type']){
				$this->error('required_null');
			}
			
			//check sign
			$sign = trim($_POST['sign']);
			$id = intval($_POST['id']);
			$row = $this->db->table('image_setting')->where("sign='$sign'")->get();
			if($row && $row['id'] != $id){
				$this->error('sign_repeated');
			}
			
			$data = array(
				'name_key_' => trim($_POST['name_key_']),
				'name' => trim($_POST['name']),
				'sign' => $sign,
				'type' => $_POST['type'],
				'thumbnails' => intval($_POST['thumbnails']),
				'width' => intval($_POST['width']),
				'height' => intval($_POST['height']),
				'watermark' => intval($_POST['watermark']),
				'watermark_img' => trim($_POST['watermark_img']),
				'watermark_pos' => $_POST['watermark_pos'],
				'watermark_alpha' => intval($_POST['watermark_alpha']),
				'status' => $_POST['status']
			);
			
			if($id){
				//check if system
				$setting = $this->db->table('image_setting')->where("id=$id")->get();
				$if_sys = $setting['if_sys'];
				if($if_sys == 1){
					$data = array(
						'name_key_' => trim($_POST['name_key_']),
						'name' => trim($_POST['name']),
						'thumbnails' => intval($_POST['thumbnails']),
						'width' => intval($_POST['width']),
						'height' => intval($_POST['height']),
						'watermark' => intval($_POST['watermark']),
						'watermark_img' => trim($_POST['watermark_img']),
						'watermark_pos' => $_POST['watermark_pos'],
						'watermark_alpha' => intval($_POST['watermark_alpha']),
					);
				}elseif($data['sign'] != $setting['sign']){
					//rename filename
					$path = BASE_PATH.$this->model('image')->img_path.'/';
					rename($path.$setting['sign'], $path.$data['sign']);
				}
				
				$this->mdata('image_setting')->where("id=$id")->save($data);
			}else{
				$id = $this->mdata('image_setting')->add($data);
			}
			
			$this->ok('edit_success', url('imagesetting/index'));
		}
	}
	
	public function del()
	{
		$id = trim($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$status = $this->model('image')->delsetting($id);
		if($status){
			$this->ok('delete_done', url('imagesetting/index'));
		}else{
			$this->error('delete_error');
		}
		
	}
}

?>