<?php
/*
*	@comment.php
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

class comment extends model
{
	public $list_num = 10;
	
	public function get_num($goods_id)
	{
		if(!$goods_id){
			return false;
		}
		$field = "count(*) as num";
		$row = $this->db->table('comments')->field($field)->where("goods_id=$goods_id and status=1")->get();
		return $row['num'];
	}
	
	public function get_score($goods_id)
	{
		$score = array(
			'score' => 5,
			'number' => 0,
			'percent' => 100,
		);
		
		if(!$goods_id){
			return $score;
		}
		
		$field = "count(*) as num, sum(score) as scores";
		$where = "comments_id in (
					select id from ".$this->db->tbname('comments')." where goods_id=$goods_id and status=1
				)";
		$row = $this->db->table('comments_summary')->field($field)->where($where)->get();
		if($row['num'] > 0){
			$score['score'] = round($row['scores']/$row['num'], 1);
			$score['number'] = $row['num'];
			$score['percent'] = ($score['score']/5)*100;
		}
		
		return $score;
	}
	
	public function get_comments_num($goods_id)
	{
		return $this->db->table('comments')->where("goods_id=$goods_id and status=1")->count();
	}
	
	public function get_comments($goods_id, $limit=0)
	{
		if(!$limit){
			$limit = $this->list_num;
		}
		
		$summarys = array();
		$summarys_list = $this->get_summarys();
		foreach($summarys_list as $v){
			$summarys[$v['id']] = $v['name'];
		}
		
		$list = $this->db->table('comments')->where("goods_id=$goods_id and status=1")->limit($limit)->getlist();
		foreach($list as $k=>$v){
			$list[$k]['time'] = $this->model('common')->date_format($v['time']);
			
			$scores = array();
			$comments_summary = $this->db->table('comments_summary')->where("comments_id=".$v['id'])->getlist();
			foreach($comments_summary as $v){
				$summary_id = $v['summary_id'];
				$scores[] = array(
					'name' => $summarys[$summary_id],
					'score' => $v['score'],
					'percent' => ($v['score']/5)*100,
				);
			}
			
			$list[$k]['scores'] = $scores;
		}
		return $list;
	}
	
	public function get_summarys($group_id = 0)
	{
		$summarys = $this->cache('summarys_'.$group_id);
		if(!$summarys){
			$where = 'status=1';
			if($group_id > 0){
				$where .= " and id in (select summary_id from ".$this->db->tbname('group_summary')." where group_id=$group_id)";
			}
			$summarys = $this->model('mdata')->table('summary')->where($where)->getlist();
			
			$this->cache('summarys_'.$group_id, $summarys);
		}
		return $summarys;
	}
	
	public function add($goods_id, $username, $content, array $score)
	{
		$setting = &$this->model('common')->setting();
		$status = intval($setting['comment_accept']);
		
		$comment = array(
			'goods_id' => $goods_id,
			'username' => $username,
			'language' => cookie('lang'),
			'content' => $content,
			'time' => time(),
			'status' => $status,
		);
		$id = $this->db->table('comments')->insert($comment);
		foreach($score as $k=>$v){
			$summary = array(
				'comments_id' => $id,
				'summary_id' => $k,
				'score' => $v,
			);
			$this->db->table('comments_summary')->insert($summary);
		}
		return $id;
	}
}
?>