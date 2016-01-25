<?php
/*
*	@db.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This library is free software; you can redistribute it and/or
*	modify it under the terms of the GNU Lesser General Public
*	License as published by the Free Software Foundation; either
*	version 2.1 of the License, or (at your option) any later version.

*	This library is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
*	GNU Lesser General Public License for more details.
*/

class db
{
	public $conn = null;
	public $driver = 'mysql';
	public $prefix = '';
	
	protected $host;
	protected $user;
	protected $pwd;
	protected $dbname;
	protected $charset;
	
	protected $table = '';
	protected $as = '';
	protected $fields = '*';
	protected $join = '';
	protected $where = '';
	protected $group = '';
	protected $order = '';
	protected $limit = '';
	
	public function set_driver($driver)
	{
		if($driver){
			if($driver == 'mysqli'){
				$driver = 'mysql_mysqli';
			}
			$this->driver = $driver;
		}
	}
	
	public function setting($host, $user, $pwd, $dbname, $charset = 'utf8')
	{
		$this->host = $host;
		$this->user = $user;
		$this->pwd = $pwd;
		$this->dbname = $dbname;
		$this->charset = $charset;
	}
	
	public function connect()
	{
		$script = CORE_PATH .'/db/'.$this->driver.'.php';
		if(!file_exists($script)){
			error("Can not find the driver: $script");
		}else{
			require_once($script);
			$this->conn = new $this->driver;
			return $this->conn->connect($this->host, $this->user, $this->pwd, $this->dbname, $this->charset);
		}
	}
	
	protected function check_connect()
	{
		if($this->conn == null){
			$this->connect();
		}
	}
	
	public function prefix($prefix)
    {
        $this->prefix = $prefix;
		return $this;
    }
	
	public function query($sql)
	{
		$this->check_connect();
		return $this->conn->query($sql);
	}
	
	public function fetch($query)
    {
        return $this->conn->fetch($query);
    }
	
	public function insert_id()
    {
        return $this->conn->insert_id();
    }
	
	public function table($name, $as = null)
    {
        if(!$name){
			$this->table = '';
		}else{
			$this->table = $this->prefix.$name;
			$this->as = $as ? $as : '';
			$this->fields = '*';
			$this->where = '';
			$this->join = '';
			$this->group = '';
			$this->order = '';
			$this->limit = '';
		}
		return $this;
    }
	
	public function tbname($name)
    {
		return $this->prefix.$name;
	}
	
	public function field($fields)
    {
        $this->fields = $fields;
		return $this;
    }
	
	public function addfield($fields)
    {
        if($fields){
			$this->fields .= ($this->fields ? ',' : '').$fields;
		}
		return $this;
    }
	
	public function where($where)
    {
        if(is_array($where)){
			$this->where = implode(' and ', $where);
		}else{
			$this->where = $where;
		}
		return $this;
    }
	
	public function group($group)
    {
        $this->group = $group;
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
	
	protected function check($n=0)
    {
		if(!$this->table){
			error('No table selected');
		}
		if($n>0 && !$this->where){
			error('No where setted');
		}
    }
	
	public function escape($str)
    {
        return mysql_real_escape_string($str);
    }
	
	public function getsql()
    {
		$this->check();
		
		$sql = 'select '.($this->fields ? $this->fields : '*')
				.' from `'.$this->table.'`'.($this->as ? ' as '.$this->as : '')
				.($this->join ? ' '.$this->join.' ' : '')
				.($this->where ? ' where '.$this->where : '')
				.($this->group ? ' group by '.$this->group : '')
				.($this->order ? ' order by '.$this->order : '')
				.($this->limit ? ' limit '.$this->limit : '');
		return $sql;
	}
	
	public function get()
    {
		$query = $this->query($this->getsql());
		return $this->fetch($query);
    }
	
	public function getval($key)
    {
		$rs = $this->field($key)->limit(1)->get();
		return $rs[$key];
    }
	
	public function count()
    {
		$rs = $this->field('count(*) as count_num')->get();
		return $rs['count_num'];
    }
	
	public function getlist()
    {
        $query = $this->query($this->getsql());
		return $this->conn->fetch_all($query);
    }
	
	public function insert($data)
    {
		$this->check();
		if(!is_array($data)){
			return 0;
		}
		$fields = $values = '';
		foreach($data as $k=>$v){
			$fields .= ($fields ? "," : "").$k;
			$values .= ($values ? "," : "")."'$v'";
		}
		$sql = 'insert into `'.$this->table."` ($fields) values ($values)";
		$this->query($sql);
		return $this->insert_id();
	}
	
	public function update($data)
    {
		$this->check();
		if(!$data){
			return 0;
		}
		
		if(is_array($data)){
			$str = '';
			foreach($data as $k=>$v){
				$str .= ($str ? "," : "")."`$k`='$v'";
			}
		}else{
			$str = $data;
		}
		
		$sql = 'update `'.$this->table."` set $str".($this->where ? ' where '.$this->where : '');
		$query = $this->query($sql);
		return $this->conn->affected_rows($query);
	}
	
	public function addnum($field, $num)
    {
		$this->check();
		$num = intval($num);
		if(!$field || $num == 0){
			return 0;
		}
		$sql = 'update `'.$this->table."` set ";
		if($num > 0){
			$sql .= "`$field` = `$field` + $num";
		}else{
			$num = 0 - $num;
			$sql .= "`$field` = `$field` - $num";
		}
		$sql .= ($this->where ? ' where '.$this->where : '');
		$sql .= ($this->limit ? ' limit '.$this->limit : '');
		
		$query = $this->query($sql);
		return $this->conn->affected_rows($query);
	}
	
	public function delete()
    {
		$this->check(1);
		$sql = 'delete from `'.$this->table.'` where '.$this->where
				.($this->group ? ' group by '.$this->group : '')
				.($this->order ? ' order by '.$this->order : '')
				.($this->limit ? ' limit '.$this->limit : '');
		$query = $this->query($sql);
		return $this->conn->affected_rows($query);
	}
	
	public function join($tyle, $table, $as, $on)
    {
        $this->join .= " $tyle join ".$this->tbname($table).($as ? " as $as " : "")." on $on";
		return $this;
    }
	
	public function leftjoin($table, $as, $on)
    {
		return $this->join('left', $table, $as, $on);
	}
	
	public function rightjoin($table, $as, $on)
    {
		return $this->join('right', $table, $as, $on);
	}
	
	public function innerjoin($table, $as, $on)
    {
		return $this->join('inner', $table, $as, $on);
	}
	
	public function close()
    {
        return $this->conn->close();
    }
	
	public function error()
    {
        return $this->conn->error();
    }
	
	public function _new()
    {
        $new_db = new self();
		$new_db->conn = $this->conn;
		$new_db->prefix = $this->prefix;
		return $new_db;
    }
}
?>