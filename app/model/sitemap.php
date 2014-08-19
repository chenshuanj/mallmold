<?php
/*
*	@sitemap.php
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

class sitemap extends model
{
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
		$setting = &$this->model('common')->setting();
		$config = $this->getconfig();
		$lastmod = date('Y-m-d');
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
					<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
							xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
							xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
							http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
		$xml .= '<url>
					<loc>'.htmlspecialchars(url('/')).'</loc>
					<priority>'.$config['index_priority'].'</priority>
					<lastmod>'.$lastmod.'</lastmod>
					<changefreq>'.$config['index_frequency'].'</changefreq>
				</url>';
		$cate_list = $this->db->table('goods_cate')->getlist();
		foreach($cate_list as $v){
			$url = $this->model('urlkey')->geturl('id', $v['id'], $v['urlkey']);
			$xml .= '<url>
						<loc>'.htmlspecialchars(url($url)).'</loc>
						<priority>'.$config['cate_priority'].'</priority>
						<lastmod>'.$lastmod.'</lastmod>
						<changefreq>'.$config['cate_frequency'].'</changefreq>
					</url>';
		}
		
		$where = "status=1";
		if($setting['show_unsale'] == 0){
			$where .= " and is_sale=1";
		}
		$goods_list = $this->db->table('goods')->where($where)->getlist();
		foreach($goods_list as $v){
			$url = $this->model('urlkey')->geturl('goods_id', $v['goods_id'], $v['urlkey']);
			$xml .= '<url>
						<loc>'.htmlspecialchars(url($url)).'</loc>
						<priority>'.$config['goods_priority'].'</priority>
						<lastmod>'.$lastmod.'</lastmod>
						<changefreq>'.$config['goods_frequency'].'</changefreq>
					</url>';
		}
		
		$page_list = $this->db->table('pages')->where("urlkey<>'home'")->getlist();
		foreach($page_list as $v){
			$url = $this->model('urlkey')->geturl('page_id', $v['id'], $v['urlkey']);
			$xml .= '<url>
						<loc>'.htmlspecialchars(url($url)).'</loc>
						<priority>'.$config['page_priority'].'</priority>
						<lastmod>'.$lastmod.'</lastmod>
						<changefreq>'.$config['page_frequency'].'</changefreq>
					</url>';
		}
		
		$article_list = $this->db->table('article')->where('status=1')->getlist();
		foreach($article_list as $v){
			$url = $this->model('urlkey')->geturl('article_id', $v['article_id'], $v['urlkey']);
			$xml .= '<url>
						<loc>'.htmlspecialchars(url($url)).'</loc>
						<priority>'.$config['article_priority'].'</priority>
						<lastmod>'.$lastmod.'</lastmod>
						<changefreq>'.$config['article_frequency'].'</changefreq>
					</url>';
		}
		
		$xml .= '</urlset>';
		$file = BASE_PATH .'/sitemap.xml';
		return file_put_contents($file, $xml);
	}
}
?>