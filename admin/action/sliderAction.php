<?php


require Action('common');

class sliderAction extends commonAction
{
	public function index()
	{
		$setting = array();
		$list = $this->mdata('image_setting')->where("type='slider' and status=1")->getlist();
		foreach($list as $v){
			$setting[$v['id']] = $v['name'];
		}
		$this->view['setting'] = $setting;
		
		$list = $this->mdata('slider')->getlist();
		foreach($list as $k=>$v){
			$list[$k]['setting_id'] = explode(',', $v['setting_id']);
		}
		
		$this->view['list'] = $list;
		$this->view('slider/index.html');
	}
	
	public function add()
	{
		if(!$_POST['submit']){
			if($_GET['id']){
				$id = intval($_GET['id']);
				$data = $this->mdata('slider')->where("slider_id=$id")->get();
				$data['setting_id'] = explode(',', $data['setting_id']);
			}else{
				$data = array();
				$data['setting_id'] = array();
			}
			$this->view['data'] = $data;
			$this->view['settinglist'] = $this->mdata('image_setting')->where("type='slider' and status=1")->getlist();
			$this->view['title'] = lang('edit_slider');
			$this->view('slider/add.html');
		}else{
			if(!$_POST['name'] || !$_POST['name']){
				$this->error('required_null');
			}
			
			$setting_id = $_POST['setting_id'];
			if(!in_array(0, $setting_id)){
				$setting_id[] = 0;
				asort($setting_id);
			}
			$setting_ids = implode(',', $setting_id);
			
			$data = array(
				'name_key_' => trim($_POST['name_key_']),
				'name' => trim($_POST['name']),
				'sign' => trim($_POST['sign']),
				'setting_id' => $setting_ids,
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('slider')->where("slider_id=$id")->save($data);
			}else{
				$this->mdata('slider')->add($data);
			}
			$this->ok('edit_success', url('slider/index'));
		}
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		//delete image
		$list = $this->db->table('slider_image')->where("slider_id=$id")->getlist();
		foreach($list as $v){
			$src = $v['src'];
			$image_id = $this->db->table('images')->where("dir='$src'")->getval('id');
			if($image_id){
				$this->model('image')->delimg($image_id);
			}
		}
		$this->mdata('slider_image')->where("slider_id=$id")->delete();
		$this->mdata('slider')->where("slider_id=$id")->delete();
		$this->ok('delete_done', url('slider/index'));
	}
	
	public function image()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		$slider = $this->mdata('slider')->where("slider_id=$id")->get();
		$this->view['list'] = $this->mdata('slider_image')->where("slider_id=$id")->getlist();
		$this->view['title'] = $slider['name'] .' > '.lang('img_list');
		$this->view['slider_id'] = $id;
		$this->view('slider/image.html');
	}
	
	public function addimage()
	{
		if(!$_POST['submit']){
			if($_GET['id']){
				$id = intval($_GET['id']);
				$data = $this->mdata('slider_image')->where("id=$id")->get();
				$this->view['data'] = $data;
				$slider_id = $data['slider_id'];
			}else{
				$slider_id = $_GET['slider_id'];
				if(!$slider_id){
					$this->error('args_error');
				}
			}
			
			$slider = $this->mdata('slider')->where("slider_id=$slider_id")->get();
			
			$this->editor_header();
			$this->editor_uploadbutton('src', $data['src'], 'slider', $slider['setting_id']);
			
			$this->view['slider_id'] = $slider_id;
			$this->view['title'] = $slider['name'] .' > '.lang('edit_img');
			$this->view('slider/addimage.html');
		}else{
			if(!$_POST['slider_id']){
				$this->error('args_error');
			}
			if(!$_POST['src']){
				$this->error('img_null');
			}
			$data = array(
				'slider_id' => intval($_POST['slider_id']),
				'title_key_' => trim($_POST['title_key_']),
				'title' => trim($_POST['title']),
				'src' => trim($_POST['src']),
				'sort_order' => intval($_POST['sort_order']),
				'status' => intval($_POST['status'])
			);
			
			if($_POST['id']){
				$id = intval($_POST['id']);
				$this->mdata('slider_image')->where("id=$id")->save($data);
			}else{
				$this->mdata('slider_image')->add($data);
			}
			$this->ok('edit_success', url('slider/image?id='.$data['slider_id']));
		}
	}
	
	public function delimage()
	{
		$id = intval($_GET['id']);
		if($id>0){
			$image = $this->db->table('slider_image')->where("id=$id")->get();
			$slider_id = $image['slider_id'];
			//delete image
			$src = $image['src'];
			$image_id = $this->db->table('images')->where("dir='$src'")->getval('id');
			if($image_id){
				$this->model('image')->delimg($image_id);
			}
			$this->mdata('slider_image')->where("id=$id")->delete();
			$this->ok('delete_done', url('slider/image?id='.$slider_id));
		}else{
			$this->error('args_error');
		}
	}
}

?>