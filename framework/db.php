<?php


class db
{
	public $conn;
	public $prefix = '';
	protected $table = '';
	protected $fields = '*';
	protected $where = '';
	protected $group = '';
	protected $order = '';
	protected $limit = '';
	
	public function connect($host, $user, $pwd, $charset = 'utf8')
	{
		$this->conn = mysql_connect($host, $user, $pwd) or error("Can't Connect MySQL Server");
		mysql_query("set names '$charset'");
	}
	
	public function select_db($dbname)
	{
		return mysql_select_db($dbname);
	}
	
	public function prefix($prefix)
    {
        $this->prefix = $prefix;
		return $this;
    }
	
	public function query($sql)
	{
		$query = mysql_query($sql, $this->conn);
		if(!$query){
			exit($sql.'<br/>'.$this->error());
		}
		return $query;
	}
	
	public function fetch($query)
    {
        return mysql_fetch_assoc($query);
    }

    public function fetch_fields($query)
    {
        return mysql_fetch_field($query);
    }
	
	public function num_rows($query)
    {
        return mysql_num_rows($query);
    }
	
	public function insert_id()
    {
        return mysql_insert_id($this->conn);
    }
	
	public function num_fields($query)
    {
        return mysql_num_fields($query);
    }
	
	public function table($name)
    {
        if(!$name){
			$this->table = '';
		}else{
			$this->table = $this->prefix.$name;
			$this->fields = '*';
			$this->where = '';
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
	
	public function where($where)
    {
        $this->where = $where;
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
	
	protected function getsql()
    {
		$this->check();
		
		$sql = 'select '.($this->fields ? $this->fields : '*')
				.' from '.$this->table
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
		//$query = $this->query($this->getsql());
		//return $this->num_rows($query);
		$rs = $this->field('count(*) as count_num')->get();
		return $rs['count_num'];
    }
	
	public function getlist()
    {
        $list = array();
		$query = $this->query($this->getsql());
		while($rs = $this->fetch($query)){
			$list[] = $rs;
		}
		return $list;
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
		$sql = 'insert into '.$this->table." ($fields) values ($values)";
		$this->query($sql);
		return $this->insert_id();
	}
	
	public function update($data)
    {
		$this->check();
		if(!$data || !is_array($data)){
			return 0;
		}
		$str = '';
		foreach($data as $k=>$v){
			$str .= ($str ? "," : "")."`$k`='$v'";
		}
		$sql = 'update '.$this->table." set $str".($this->where ? ' where '.$this->where : '');
		$this->query($sql);
		return mysql_affected_rows($this->conn);
	}
	
	public function addnum($field, $num)
    {
		$this->check();
		$num = intval($num);
		if(!$field || $num == 0){
			return 0;
		}
		$sql = 'update '.$this->table." set ";
		if($num > 0){
			$sql .= "`$field` = `$field` + $num";
		}else{
			$num = 0 - $num;
			$sql .= "`$field` = `$field` - $num";
		}
		$this->query($sql);
		return mysql_affected_rows($this->conn);
	}
	
	public function delete()
    {
		$this->check(1);
		$sql = 'delete from '.$this->table.' where '.$this->where
				.($this->group ? ' group by '.$this->group : '')
				.($this->order ? ' order by '.$this->order : '')
				.($this->limit ? ' limit '.$this->limit : '');
		$this->query($sql);
		return mysql_affected_rows($this->conn);
	}
	
	public function close()
    {
        return mysql_close($this->conn);
    }
	
	public function error()
    {
        return mysql_error($this->conn);
    }
}
?>