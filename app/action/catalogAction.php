<?php
/*
*	@catalogAction.php
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

require Action('common');

class catalogAction extends commonAction
{
	public function index()
	{
		$id = $this->model('urlkey')->getid('id');
		if(!$id){
			$this->_404();
			return;
		}
		
		$cate = $this->model('catalog')->get($id);
		if(!$cate){
			$this->_404();
			return;
		}
		
		$attr_args = $this->attributes_args();
		
		$where = "goods_id in (select goods_id from ".$this->db->tbname('goods_cate_val')." where cate_id=$id)";
		if($attr_args){
			$count = count($attr_args);
			$av_ids = implode(',', $attr_args);
			$where .= " and goods_id in (
							select goods_id as num from ".$this->db->tbname('goods_attr')." 
							where av_id in ($av_ids) group by goods_id having count(*)>=$count 
						)";
		}
		$total = $this->model('goods')->get_count($where);
		$pager = $this->load('lib/page')->pager($total);
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		$list = $this->model('goods')->getlist($where, null, $limit);
		
		$this->view['id'] = $id;
		$this->view['list'] = $list;
		$this->view['img_sign'] = $this->model('image')->getsignbyid('goods_list_sid');
		$this->view['pager'] = $pager;
		$this->view['cate'] = $cate;
		$this->view['html_title'] = $cate['meta_title'] ? $cate['meta_title'] : $cate['name'];
		$this->view['meta_description'] = $cate['meta_description'];
		$this->view['meta_keywords'] = $cate['meta_keywords'];
		$this->view['map'] = $this->model('catalog')->cate_map($id);
		$this->view('catalog/index.html');
	}
	
	public function search()
	{
		$keyword = $_POST['keyword'] ? trim($_POST['keyword']) : trim(urldecode($_GET['keyword']));
		$keyword = strip_tags($keyword);
		
		$attr_args = $this->attributes_args($keyword);
		$where = '1=1';
		$list = array();
		if($keyword){
			$keyword = preg_replace('/\s{2,}/', '+', $keyword);
			$keys = explode('+', $keyword);
			$this->model('goods')->save_keywords($keys);
			$match_list = $this->model('goods')->search_list($keys);
			if($match_list){
				$where .= " and goods_id in (".implode(',', $match_list).")";
			}else{
				$where = '1=0';
			}
		}
		
		if($where != '1=0' && $attr_args){
			$count = count($attr_args);
			$av_ids = implode(',', $attr_args);
			$where .= " and goods_id in (
						select goods_id as num from ".$this->db->tbname('goods_attr')." 
						where av_id in ($av_ids) group by goods_id having count(*)>=$count 
					)";
		}
		
		$total = $this->model('goods')->get_count($where);
		$pager = $this->load('lib/page')->addargs(array('keyword'=>$keyword))->pager($total);
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		$list = $this->model('goods')->getlist($where, null, $limit);
		
		$title = lang('Search'). ': '.$this->view['filter_title'];
		
		$this->view['img_sign'] = $this->model('image')->getsignbyid('goods_list_sid');
		$this->view['keyword'] = $keyword;
		$this->view['html_title'] = $title;
		$this->view['map'] = array(array('title' => $title));
		$this->view['list'] = $list;
		$this->view['pager'] = $pager;
		$this->view['catelist'] = $this->model('catalog')->get_catelist(0);
		$this->view('catalog/search.html');
	}
	
	private function attributes_args($keyword = '')
	{
		$attributes = $this->model('catalog')->get_attributes();
		
		$attr_args = array();
		foreach($attributes as $v){
			$code = $v['code'];
			if($_GET[$code]){
				$attr_args[$code] = intval($_GET[$code]);
				$this->model('statistic')->add_attr_click($v['attr_id']);
			}
		}
		
		$base_url = $this->model('urlkey')->getaction('id');
		$filter_title = $keyword;
		foreach($attributes as $k=>$v){
			$code = $v['code'];
			$args = $attr_args;
			if($args[$code]){
				$av_id = $args[$code];
				$filter_title .= ($filter_title ? ' + ' : '').'['.$v['values'][$av_id]['title'].']';
				unset($args[$code]);
			}
			
			$urlargs = array();
			if($args){
				foreach($args as $key=>$val){
					$urlargs[] = "$key=$val";
				}
			}
			
			if($keyword){
				$urlargs[] = "keyword=$keyword";
			}
			$attributes[$k]['base_args'] = $base_url.($urlargs ? '?'.implode('&', $urlargs).'&' : '?');
		}
		
		$this->view['attr_args'] = $attr_args;
		$this->view['filter_title'] = $filter_title;
		$this->view['attributes'] = $attributes;
		return $attr_args;
	}
}

?>