<?php
/*
*	@image.php
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

class image extends model
{
	public $upload_path = '/upload/image';
	public $img_path = '/images';
	
	public function gettypes()
	{
		$types = array(
			'goods_imgs' => lang('goods_imgs'),
			'goods_main_img' => lang('goods_main_img'),
			'goods_desc' => lang('goods_desc_img'),
			'goods_cate' => lang('goods_cate_img'),
			'article_img' => lang('article_img'),
			'article_desc' => lang('article_desc_img'),
			'slider' => lang('slider'),
			'other' => lang('other')
		);
		return $types;
	}
	
	public function add($src, $type, $setting_id='0')
	{
		if(!$src || !$type){
			return null;
		}
		
		$types = $this->gettypes();
		if(!$types[$type]){
			$type = 'other';
		}
		
		$data = array(
			'dir' => $src,
			'type' => $type,
			'addtime' => time(),
		);
		$this->db->table('images')->insert($data);
		
		if($type == 'slider' && $setting_id=='0'){
			return $src;
		}
		
		$image = $src;
		if($type != 'other'){
			//basename
			$node = explode('/', $src);
			$n = count($node)-1;
			$file_name = $node[$n];
			$date = $node[$n-1];
			
			$where = $setting_id=='0' ? "type='$type' and status=1" : "id in ($setting_id)";
			$list = $this->db->table('image_setting')->where($where)->getlist();
			foreach($list as $v){
				$sign = $v['sign'];
				$path = "/images/$sign/";
				if(!file_exists(BASE_PATH.$path)){
					mkdir(BASE_PATH.$path);
				}
				$path .= "$date/";
				if(!file_exists(BASE_PATH.$path)){
					mkdir(BASE_PATH.$path);
				}
				
				$path .= $file_name;
				if($v['thumbnails'] && $v['width'] && $v['height']){
					$this->load('lib/image_gd')->thumbnail(BASE_PATH.$src, BASE_PATH.$path, $v['width'], $v['height']);
				}else{
					copy(BASE_PATH.$src, BASE_PATH.$path);
				}
				
				if($v['watermark'] && $v['watermark_img']){
					$this->load('lib/image_gd')->water_mark(BASE_PATH.$path, $v['watermark_img'], $v['watermark_pos'], $v['watermark_alpha']);
				}
				
				if($v['if_sys'] == 1){
					$image = $path;
				}
			}
		}
		return $image;
	}
	
	public function check_img($src, $type)
	{
		if(!$src || !$type){
			return null;
		}
		
		$n = $this->db->table('images')->where("dir='$src' and type='$type'")->count();
		if($n < 1){
			return $this->add($src, $type);
		}else{
			return $src;
		}
	}
	
	public function delsetting($id)
	{
		$setting = $this->db->table('image_setting')->where("id=$id")->get();
		if($setting['if_sys'] == 1){
			return false;
		}
		
		$path = BASE_PATH.$this->img_path.'/'.$setting['sign'];
		
		$this->batch_del($id);
		$this->db->table('image_setting')->where("id=$id")->delete();
		return $this->load('lib/dir')->deldir($path);
	}
	
	public function delimg($id)
	{
		$data = $this->db->table('images')->where("id=$id")->get();
		$filename = str_replace($this->model('image')->upload_path, '', $data['dir']);
		$type = $data['type'];
		$setting = $this->model('mdata')->table('image_setting')->where("type='$type' and status=1")->getlist();
		foreach($setting as $k=>$v){
			$sign = $v['sign'];
			$path = BASE_PATH.$this->model('image')->img_path.'/'.$sign;
			if(file_exists($path.$filename)){
				unlink($path.$filename);
			}
		}
		
		if(file_exists(BASE_PATH .$data['dir'])){
			unlink(BASE_PATH .$data['dir']);
		}
		
		return $this->db->table('images')->where("id=$id")->delete();
	}
	
	public function remake($id, $where='')
	{
		$setting = $this->db->table('image_setting')->where("id=$id")->get();
		$path = BASE_PATH.$this->img_path.'/'.$setting['sign'];
		if(!is_dir($path)){
			mkdir($path);
		}
		
		$type = $setting['type'];
		$where .= ($where ? ' and ' : '')."type='$type'";
		$list = $this->db->table('images')->where($where)->getlist();
		foreach($list as $v){
			$filename = str_replace($this->upload_path, '', $v['dir']);
			if($setting['thumbnails'] && $setting['width'] && $setting['height']){
				$this->load('lib/image_gd')->thumbnail(BASE_PATH.$v['dir'], $path.$filename, $setting['width'], $setting['height']);
			}else{
				//check path
				$this->load('lib/dir')->checkdir($path.$filename);
				copy(BASE_PATH.$v['dir'], $path.$filename);
			}
			
			if($setting['watermark'] && $setting['watermark_img']){
				$this->load('lib/image_gd')->water_mark($path.$filename, $setting['watermark_img'], $setting['watermark_pos'], $setting['watermark_alpha']);
			}
		}
		return true;
	}
	
	public function batch_del($id, $where='')
	{
		$setting = $this->db->table('image_setting')->where("id=$id")->get();
		$path = BASE_PATH.$this->img_path.'/'.$setting['sign'];
		$type = $setting['type'];
		$where .= ($where ? ' and ' : '')."type='$type'";
		$list = $this->db->table('images')->where($where)->getlist();
		foreach($list as $v){
			$filename = str_replace($this->upload_path, '', $v['dir']);
			if(file_exists($path.$filename)){
				unlink($path.$filename);
			}
		}
		return true;
	}
}
?>