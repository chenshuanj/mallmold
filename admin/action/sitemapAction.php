<?php
/*
*	@sitemapAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require Action('common');

class sitemapAction extends commonAction
{
	public function index()
	{
		$this->view['data'] = $this->model('sitemap')->getconfig();
		$this->view['changefreq'] = $this->model('sitemap')->changefreq();
		$this->view['host'] = $_SERVER['HTTP_HOST'];
		$this->view['title'] = lang('Sitemap');
		$this->view('sitemap/index.html');
	}
	
	public function update()
	{
		$setting = $this->model('sitemap')->getconfig();
		$data = $_POST['data'];
		foreach($data as $k=>$v){
			if(isset($setting[$k])){
				$this->db->table('sitemap')->where("name='$k'")->update(array('val' => $v));
			}
		}
		
		if($_POST['generate'] == 1){
			$this->model('sitemap')->generate();
		}
		
		$this->ok('edit_success', url('sitemap/index'));
	}
}

?>