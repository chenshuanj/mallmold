<?php


class slider extends model
{
	public function get($sign)
	{
		if(!$sign)
			return null;
		
		$list = $this->cache('slider_'.$sign);
		if(!$list){
			$slider = $this->db->table('slider')->where("sign='$sign'")->get();
			if(!$slider){
				return null;
			}
			$setting_id = $slider['setting_id'];
			$list = $this->model('mdata')->table('slider_image')->where('slider_id='.$slider['slider_id'])->getlist();
			foreach($list as $k=>$v){
				$list[$k]['src'] = $this->model('image')->getimgbyid($setting_id, $v['src']);
			}
			$this->cache('slider_'.$sign, $list);
		}
		return $list;
	}
}
?>