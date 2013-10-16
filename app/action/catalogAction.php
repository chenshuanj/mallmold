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
			$this->error('404 not found');
			return;
		}
		
		$cate = $this->model('catalog')->get($id);
		if(!$cate){
			$this->error('404 not found');
			return;
		}
		
		$attr_args = array();
		foreach($_GET as $k=>$v){
			if(stripos($k, 'attr_') === 0){
				$attr_id = intval(substr($k, 5));
				$attr_args[$attr_id] = intval($v);
				
				$this->model('statistic')->add_attr_click($attr_id);
			}
		}
		
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
		
		$attributes = $this->model('catalog')->get_attributes();
		foreach($attributes as $k=>$v){
			$args = $attr_args;
			$urlargs= "id=$id";
			$attr_id = $v['attr_id'];
			unset($args[$attr_id]);
			if($args){
				foreach($args as $key=>$val){
					$urlargs .= '&attr_'.$key.'='.$val;
				}
			}
			$attributes[$k]['base_args'] = $urlargs;
		}
		
		$goods_list_sid = $this->setting['goods_list_sid'];
		$this->view['img_sign'] = $this->model('image')->getsignbyid($goods_list_sid);
		
		$this->view['id'] = $id;
		$this->view['list'] = $list;
		$this->view['pager'] = $pager;
		$this->view['cate'] = $cate;
		$this->view['attributes'] = $attributes;
		$this->view['attr_args'] = $attr_args;
		$this->view['html_title'] = $cate['meta_title'] ? $cate['meta_title'] : $cate['name'];
		$this->view['meta_description'] = $cate['meta_description'];
		$this->view['meta_keywords'] = $cate['meta_keywords'];
		$this->view['map'] = $this->model('catalog')->cate_map($id);
		$this->view('catalog/index.html');
	}
	
	public function search()
	{
		$keyword = trim($_REQUEST['keyword']);
		
		$attr_args = array();
		foreach($_GET as $k=>$v){
			if(stripos($k, 'attr_') === 0){
				$attr_id = intval(substr($k, 5));
				$attr_args[$attr_id] = intval($v);
				
				$this->model('statistic')->add_attr_click($attr_id);
			}
		}
		
		$where = '1=1';
		$list = array();
		if($keyword){
			$keyword = preg_replace('/\s{2,}/', '+', $keyword);
			$keys = explode('+', $keyword);
			$this->model('goods')->save_keywords($keys);
			
			$match_list = $this->model('goods')->search_list();
			$match = array();
			foreach($match_list as $v){
				foreach($keys as $key){
					if(strpos($v['title'], $key) !== false){
						$match[] = $v['goods_id'];
						break;
					}
				}
			}
			
			$total = count($match);
			if($total > 0){
				$where .= " and goods_id in (".implode(',', $match).")";
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
		
		$title = $keyword;
		$attributes = $this->model('catalog')->get_attributes();
		foreach($attributes as $k=>$v){
			$args = $attr_args;
			$urlargs = $keyword ? "keyword=$keyword" : '';
			$attr_id = $v['attr_id'];
			if($args[$attr_id]){
				$av_id = $args[$attr_id];
				$title .= ($title ? ' + ' : '').'['.$v['values'][$av_id]['title'].']';
				unset($args[$attr_id]);
			}
			if($args){
				foreach($args as $key=>$val){
					$urlargs .= '&attr_'.$key.'='.$val;
				}
			}
			$attributes[$k]['base_args'] = $urlargs;
		}
		
		$goods_list_sid = $this->setting['goods_list_sid'];
		$this->view['img_sign'] = $this->model('image')->getsignbyid($goods_list_sid);
		
		$this->view['keyword'] = $keyword;
		$this->view['html_title'] = $title;
		$this->view['map'] = array(array('title' => $title));
		$this->view['list'] = $list;
		$this->view['pager'] = $pager;
		$this->view['catelist'] = $this->model('catalog')->get_catelist(0);
		$this->view['attributes'] = $attributes;
		$this->view['attr_args'] = $attr_args;
		$this->view('catalog/search.html');
	}
}

?>