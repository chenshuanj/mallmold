<?php
/*
*	@page.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

class page
{
	public $page = 0;
	public $url = '';
	public $args = array();
	public $pagesize = 15;
	public $page_key = 'page';
	
	public function __construct($setting = array())
    {
		$setting['url'] && $this->url = $setting['url'];
		$setting['page'] && $this->page = $setting['page'];
		$setting['pagesize'] && $this->pagesize = $setting['pagesize'];
		$setting['page_key'] && $this->page_key = $setting['page_key'];
	}
	
	public function geturl()
    {
		if($this->url){
			$url = $this->url .(strpos($url, '?') ? '&' : '?').$this->page_key.'=';
		}else{
			$this->args = array_merge($_GET, $this->args);
			unset($this->args[$this->page_key]);
			$url = $_SERVER['SCRIPT_NAME'].($this->args ? '?'.http_build_query($this->args).'&' : '?').$this->page_key.'=';
		}
		return $url;
	}
	
	public function addargs(array $arr)
    {
		$this->args = array_merge($this->args, $arr);
		return $this;
	}
	
	public function getpage()
    {
		if(!$this->page){
			$this->page = intval($_GET[$this->page_key]);
		}
		
		return $this->page;
	}
	
	public function pager($total)
	{
		$total = $total<0 ? 0 : intval($total);
		$pagesize = $this->pagesize;
		$pages = ceil($total/$pagesize);
		$page = $this->getpage();
		
		if($page < 1){
			$page = 1;
		}
		
		$pre = $page > 1 ? $page-1 : 0;
		$p = $next = $page < $pages ? $page+1 : 0;
		
		$list = array();
		if($p>$page && $p < $pages){
			$n = 0;
			while($n<5 && $p<=$pages){
				$list[] = $p++;
				$n++;
			}
		}
		
		$end = ($p>$page && $p < $pages) ? $pages : 0;
		
		return array(
			'total' => $total,
			'pagesize' => $pagesize,
			'pages' => $pages,
			'page' => $page,
			'pre' => $pre,
			'next' => $next,
			'first' => ($page>2 ? 1 : 0),
			'end' => $end,
			'list' => $list,
			'url' => $this->geturl(),
		);
	}
}
?>