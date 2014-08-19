<?php
/*
*	@imageAction.php
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

class imageAction extends commonAction
{
	public function index()
	{
		$where = '1=1';
		
		$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
		$types = $this->model('image')->gettypes();
		if($type && !$types[$type]){
			$type = '';
		}
		$where .= $type ? " and type='$type'" : "";
		
		$keyword = trim($_POST['keyword']);
		if($keyword){
			$where .= " and dir like '%$keyword%'";
		}
		
		$total = $this->db->table('images')->where($where)->count();
		$this->pager($total);
		
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$list = $this->db->table('images')->limit($limit)->where($where)->getlist();
		foreach($list as $k=>$v){
			$list[$k]['time'] = date('Y-m-d H:i:s', $v['addtime']);
		}
		
		if($type){
			$this->view['setting'] = $this->mdata('image_setting')->where("type='$type'")->getlist();
		}
		
		$this->view['list'] = $list;
		$this->view['type'] = $type;
		$this->view['types'] = $types;
		$this->view['title'] = lang('imagelist');
		$this->view('image/index.html');
	}
	
	public function edit()
	{
		$id = trim($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$data = $this->db->table('images')->where("id=$id")->get();
		$filename = str_replace($this->model('image')->upload_path, '', $data['dir']);
		$type = $data['type'];
		$setting = $this->mdata('image_setting')->where("type='$type' and status=1")->getlist();
		foreach($setting as $k=>$v){
			$sign = $v['sign'];
			$path = BASE_PATH.$this->model('image')->img_path.'/'.$sign;
			if(file_exists($path.$filename)){
				$setting[$k]['image'] = $this->model('image')->img_path.'/'.$sign.$filename;
			}
		}
		
		$this->view['data'] = $data;
		$this->view['setting'] = $setting;
		$this->view['title'] = lang('view_img');
		$this->view('image/edit.html');
	}
	
	public function update()
	{
		$id = intval($_POST['id']);
		$plan_id = $_POST['plan_id'];
		if(!$id || !$plan_id){
			$this->error('args_error');
		}
		
		$where = "id=$id";
		foreach($plan_id as $sid){
			$this->model('image')->remake($sid, $where);
		}
		
		$this->ok('edit_success', url('image/edit?id='.$id));
	}
	
	public function batch()
	{
		$type = intval($_POST['type']);
		$plan_id = intval($_POST['plan_id']);
		$image_id = $_POST['image_id'];
		$do = intval($_POST['do']); //1 redraw, 2 delete
		if(!$type && !$do && !$plan_id){
			$this->error('args_error');
		}
		
		$where = '';
		if($image_id){
			$where = "id in (".implode(',', $image_id).")";
		}
		
		if($do == 1){
			$this->model('image')->remake($plan_id, $where);
		}elseif($do == 2){
			$this->model('image')->batch_del($plan_id, $where);
		}
		
		$this->ok('edit_success', url('image/index?type='.$type));
	}
	
	public function del()
	{
		$id = trim($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$this->model('image')->delimg($id);
		$this->ok('delete_done', url('image/index'));
	}
}

?>