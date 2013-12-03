<?php
/*
*	@urlkey.php
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

class urlkey extends model
{
	public $extension = '.html';
	
	public function model_keys()
	{
		return array(
			'id' => 'catalog',
			'goods_id' => 'goods',
			'article_id' => 'article',
			'cate_id' => 'list',
			'page_id' => 'page',
		);
	}
	
	public function geturl($item_key, $item_id, $urlkey='')
	{
		$router = &$GLOBALS['router'];
		$type = isset($router['type']) ? $router['type'] : 0;
		$model_keys = $this->model_keys();
		$model = $model_keys[$item_key];
		if($type == 0){
			return "$model/index?$item_key=$item_id";
		}else{
			return "$model/".($urlkey ? $urlkey : "index/$item_key/$item_id").$this->extension;
		}
	}
	
	public function getid($item_key)
	{
		if($_GET[$item_key]){
			return intval($_GET[$item_key]);
		}
		
		$model_keys = $this->model_keys();
		$model = $model_keys[$item_key];
		$urlkey = preg_replace("/\W/", '-', trim($_GET['a']));
		$urlkey = preg_replace('/'.$this->extension.'$/i', '', $urlkey);
		$item_id = $this->db->table('urlkey')->where("model='$model' and urlkey='$urlkey'")->getval('item_id');
		return $item_id ? $item_id : 0;
	}
}
?>