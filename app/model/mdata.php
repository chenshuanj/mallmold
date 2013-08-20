<?php
/*
*	@mdata.php
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

class mdata extends model
{
	protected $table;
	protected $field;
	protected $where;
	protected $order;
	protected $limit;
	
	public function table($table)
	{
		$this->table = $table;
		$this->field = '*';
		$this->where = $this->order = $this->limit = '';
		return $this;
	}
	
	public function field($field)
	{
		$this->field = ($field ? $field : '*');
		return $this;
	}
	
	public function where($where)
	{
		$this->where = $where;
		return $this;
	}
	
	public function order($order)
	{
		$this->order = $order;
		return $this;
	}
	
	public function limit($limit)
	{
		$this->limit = $limit;
		return $this;
	}
	
	public function getlist()
	{
		if(!$this->table){
			return null;
		}
		
		$list = $this->db->table($this->table)
						->field($this->field)
						->where($this->where)
						->order($this->order)
						->limit($this->limit)
						->getlist();
		foreach($list as $key=>$val){
			$list[$key] = $this->model('dictionary')->getdict($val);
		}
		return $list;
	}
	
	public function get()
	{
		if(!$this->table){
			return null;
		}
		
		$data = $this->db->table($this->table)->field($this->field)->where($this->where)->get();
		if($data){
			$data = $this->model('dictionary')->getdict($data);
		}
		return $data;
	}
}
?>