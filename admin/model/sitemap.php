<?php


class sitemap extends model
{
	public function changefreq()
	{
		return array(
			'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'
		);
	}
	
	public function getconfig()
	{
		$data = array();
		$list = $this->db->table('sitemap')->getlist();
		foreach($list as $v){
			$key = $v['name'];
			$data[$key] = $v['val'];
		}
		return $data;
	}
	
	public function generate()
	{
		$this->model('event')->add('backend', 'sitemap.generate');
	}
}
?>