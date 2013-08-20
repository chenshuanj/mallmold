<?php
/*
*	@comment.php
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

class comment extends model
{
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
		if(!$goods_id){
			return false;
		}
		
		$field = "count(*) as num, sum(score) as scores";
		$where = "comments_id in (
					select id from ".$this->db->tbname('comments')." where goods_id=$goods_id and status=1
				)";
		$row = $this->db->table('comments_summary')->field($field)->where($where)->get();
		if($row['num'] == 0){
			return 0;
		}else{
			return round($row['scores']/$row['num'], 1);
		}
	}
	
	public function get_comments($goods_id)
	{
		$list = $this->db->table('comments')->where("goods_id=$goods_id and status=1")->getlist();
		foreach($list as $k=>$v){
			$list[$k]['time'] = date('Y-m-d', $v['time']);
			$list[$k]['scores'] = $this->db->table('comments_summary')
											->where("comments_id=".$v['id'])
											->getlist();
		}
		return $list;
	}
	
	public function get_summarys()
	{
		$summarys = $this->cache('summarys');
		if(!$summarys){
			$summarys = $this->model('mdata')->table('summary')->where('status=1')->getlist();
			$this->cache('summarys', $summarys);
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