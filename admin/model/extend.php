<?php


class extend extends model
{
	public function type()
	{
		return array(
			1 => lang('string'),
			2 => lang('selection'),
			3 => lang('multiple_selection'),
			4 => lang('bool_type'),
		);
	}
	
	public function getlist($where = '')
	{
		$where .= ($where ? ' and ' : '').'status=1';
		$list = $this->model('mdata')->table('extend')->where($where)->getlist();
		foreach($list as $k=>$v){
			if($v['type']==2 || $v['type']==3){
				$list[$k]['values'] = $this->model('mdata')
											->table('extend_val')
											->where("extend_id=".$v['extend_id'])
											->order('sort_order asc')
											->getlist();
			}
		}
		return $list;
	}
}
?>