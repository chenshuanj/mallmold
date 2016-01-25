<?php
/*
*	@extend.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

class extend extends model
{
	public function type()
	{
		return array(
			1 => lang('string'),
			2 => lang('selection'),
			3 => lang('multiple_selection'),
			4 => lang('bool_type'),
			5 => lang('file'),
		);
	}
	
	public function getlist($where = '')
	{
		$where .= ($where ? ' and ' : '').'status=1';
		$list = $this->model('mdata')->table('extend')->where($where)->getlist();
		foreach($list as $k=>$v){
			if($v['type']==2 || $v['type']==3){
				$list[$k]['values'] = $this->model('mdata')
											->table('extend_val')
											->where("extend_id=".$v['extend_id'])
											->order('sort_order asc')
											->getlist();
			}
		}
		return $list;
	}
	
	public function get_type($extend_id)
	{
		return $this->db->table('extend')->where("extend_id=$extend_id")->getval('type');
	}
	
	public function get_file($extend_id)
	{
		$file = '';
		$extend_file = $_FILES['extend_upload'];
		if($extend_file['tmp_name'][$extend_id]){
			$file_name = $extend_file['name'][$extend_id];
			$save_path = '/upload/extend_file/'.$file_name;
			$this->load('lib/dir')->checkdir(BASE_PATH.$save_path);
			
			$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = strtolower(trim($file_ext));
			if(strpos($file_ext, 'php') === false){
				if(move_uploaded_file($extend_file['tmp_name'][$extend_id], BASE_PATH.$save_path)){
					$file = $save_path;
				}
			}
		}
		return $file;
	}
	
	public function del_file($goods_id, $extend_id)
	{
		$file = $this->db->table('goods_extend')->where("goods_id=$goods_id and extend_id=$extend_id")->getval('val');
		if(!$file){
			return false;
		}
		$file_path = BASE_PATH.$file;
		if(file_exists($file_path)){
			unlink($file_path);
		}
		return true;
	}
}
?>