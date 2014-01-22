<?php


class image extends model
{
	private $upload_path = '/upload/image';
	private $img_path = '/images';
	
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
	
	public function getsetting()
	{
		static $image_setting = null;
		if($image_setting){
			return $image_setting;
		}
		
		$key = 'image_setting'.($type ? '_'.$type : '');
		$image_setting = $this->cache($key, null, 0);
		if(!$image_setting){
			$list = $this->db->table('image_setting')->where('status=1')->getlist();
			$image_setting = array();
			foreach($list as $v){
				$image_setting[$v['id']] = $v;
			}
			$this->cache($key, $image_setting, 0);
		}
		return $image_setting;
	}
	
	public function getlistbytype($type)
	{
		if(!$type){
			return array();
		}
		$settings = $this->getsetting();
		$list = array();
		foreach($settings as $v){
			if($v['type'] == $type){
				$list[] = $v;
			}
		}
		
		return $list;
	}
	
	public function getsign($ids)
	{
		$signs = array();
		if($ids == '')
			return $signs;
		
		$settings = $this->getsetting();
		$ids = explode(',', $ids);
		foreach($ids as $id){
			if($settings[$id]){
				$signs[] = $settings[$id]['sign'];
			}elseif($id == '0'){
				$signs[] = 'origin';
			}
		}
		return $signs;
	}
	
	public function getsignbyid($id)
	{
		$setting = &$this->model('common')->setting();
		$is_mobile = $this->model('visitor')->is_mobile();
		if($is_mobile){
			switch($id)
			{
				case 'goods_index_sid':
				case 'goods_list_sid':
				case 'goods_view_sid':
					$id = 'mobile_goods_sid';
					break;
				case 'goods_imgs_small_sid':
					$id = 'mobile_goods_imgs_sid';
					break;
			}
		}
		
		$sid = $setting[$id];
		$image_setting = $this->getsetting();
		return $image_setting[$sid]['sign'];
	}
	
	public function getimgbyid($ids, $path)
	{
		$images = array();
		
		if(!$path)
			return $images;
			
		$filename = str_replace($this->upload_path, '', $path);
		$signs = $this->getsign($ids);
		foreach($signs as $v){
			if($v == 'origin'){
				$img = $path;
			}else{
				$img = $this->img_path.'/'.$v.$filename;
				//if not exists
				if(!file_exists(BASE_PATH .$img)){
					$this->makeimg($v, $filename);
				}
			}
			$images[$v] = $img;
		}
		return $images;
	}
	
	public function getimgbytype($type, $path)
	{
		$images = array();
		if(!$type || !$path)
			return $images;
		if($type == 'other')
			return $path;
		$info = explode('/', $path, 4);
		$filename = $info[3];
		$settings = $this->getlistbytype($type);
		foreach($settings as $v){
			$sign = $v['sign'];
			if($sign == $info[2]){
				$images[$sign] = $path;
			}else{
				$img = $this->img_path.'/'.$sign.'/'.$filename;
				if(!file_exists(BASE_PATH .$img)){
					$this->makeimg($sign, $filename);
				}
				$images[$sign] = $img;
			}
		}
		return $images;
	}
	
	public function makeimg($sign, $filename)
	{
		$settings = $this->getsetting();
		$img = '';
		foreach($settings as $v){
			if($v['sign'] == $sign){
				$origin = $this->upload_path .'/'.$filename;
				$img = $this->img_path.'/'.$sign.'/'.$filename;
				$this->load('lib/dir')->checkdir(BASE_PATH.$img);
				if($v['thumbnails'] && $v['width'] && $v['height']){
					$this->load('lib/image_gd')->thumbnail(BASE_PATH.$origin, BASE_PATH.$img, $v['width'], $v['height']);
				}else{
					copy(BASE_PATH.$origin, BASE_PATH.$img);
				}
				
				if($v['watermark'] && $v['watermark_img']){
					$this->load('lib/image_gd')->water_mark(BASE_PATH.$img, $v['watermark_img'], $v['watermark_pos'], $v['watermark_alpha']);
				}
				break;
			}
		}
		return $img;
	}
}
?>