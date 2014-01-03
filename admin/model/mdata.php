<?php


class mdata extends model
{
	protected $table;
	protected $where;
	protected $order;
	protected $limit;
	
	public function table($table)
	{
		$this->table = $table;
		$this->where = $this->order = $this->limit = '';
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
		$list = $this->db->table($this->table)
						->where($this->where)
						->order($this->order)
						->limit($this->limit)
						->getlist();
		foreach($list as $key=>$val){
			$list[$key] = $this->model('dict')->getdict($val);
		}
		return $list;
	}
	
	public function get()
	{
		$data = $this->db->table($this->table)->where($this->where)->get();
		if($data){
			$data = $this->model('dict')->getdict($data);
		}
		return $data;
	}
	
	public function add($data)
	{
		$data = $this->model('dict')->setdict($data);
		return $this->db->table($this->table)->insert($data);
	}
	
	public function save($data)
	{
		$data = $this->model('dict')->setdict($data);
		return $this->db->table($this->table)->where($this->where)->update($data);
	}
	
	public function delete()
	{
		$data = $this->db->table($this->table)->where($this->where)->get();
		$this->model('dict')->deldict($data);
		return $this->db->table($this->table)->where($this->where)->delete();
	}
}
?>