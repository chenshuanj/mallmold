<?php
/*
*	@commentsAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require Action('common');

class commentsAction extends commonAction
{
	public function index()
	{
		$where = '';
		$content = trim($_POST['content']);
		if($content){
			$where = "content like '%$content%'";
		}
		
		$total = $this->db->table('comments')->where($where)->count();
		$this->pager($total);
		
		$pager = $this->view['pager'];
		$limit = ($pager['page'] - 1)*$pager['pagesize'].','.$pager['pagesize'];
		
		$list = $this->db->table('comments')->where($where)->limit($limit)->getlist();
		foreach($list as $k=>$v){
			$list[$k]['time'] = date('Y-m-d H:i:s', $v['time']);
			$comments_id = $v['id'];
			$summary = $this->db->table('comments_summary')
								->field('count(*) as n,sum(score) as summary')
								->where("comments_id=$comments_id")
								->group('comments_id')
								->get();
			if($summary){
				$list[$k]['score'] = round($summary['summary']/$summary['n'], 1);
			}else{
				$list[$k]['score'] = lang('none_score');
			}
		}
		
		$this->view['list'] = $list;
		$this->view['content'] = $content;
		$this->view['title'] = lang('comments');
		$this->view('comments/list.html');
	}
	
	public function show()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$data = $this->db->table('comments')->where("id=$id")->get();
		$data['time'] = date('Y-m-d H:i:s', $data['time']);
		
		$goods = $this->mdata('goods')->where("goods_id=".$data['goods_id'])->get();
		$score = $this->db->table('comments_summary')->where("comments_id=".$data['id'])->getlist();
		$summary = array();
		$list = $this->mdata('summary')->getlist();
		foreach($list as $v){
			$summary[$v['id']] = $v['name'];
		}
		
		$this->view['data'] = $data;
		$this->view['goods'] = $goods;
		$this->view['score'] = $score;
		$this->view['summary'] = $summary;
		$this->view['title'] = lang('view_comments');
		$this->view('comments/show.html');
	}
	
	public function update()
	{
		$id = intval($_POST['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$status = intval($_POST['status']);
		$this->db->table('comments')->where("id=$id")->update(array('status'=>$status));
		
		$this->ok('edit_success', url('comments/show?id='.$id));
	}
	
	public function del()
	{
		$id = intval($_GET['id']);
		if(!$id){
			$this->error('args_error');
		}
		
		$this->db->table('comments_summary')->where("comments_id=$id")->delete();
		$this->db->table('comments')->where("id=$id")->delete();
		$this->ok('delete_done', url('comments/index'));
	}
}

?>