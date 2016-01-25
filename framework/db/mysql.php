<?php
/*
*	@mysql.php
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

class mysql
{
	private $conn;
	
	public function connect($host, $user, $pwd, $dbname, $charset = 'utf8')
	{
		$this->conn = mysql_connect($host, $user, $pwd) or exit("Can't connect to MySQL Server");
		mysql_query("set names '$charset'");
		mysql_select_db($dbname) or exit($this->error());
		return $this->conn;
	}
	
	public function query($sql)
	{
		$query = mysql_query($sql, $this->conn);
		if(!$query){
			error($this->error());
		}
		return $query;
	}
	
	public function fetch($query)
    {
        return mysql_fetch_assoc($query);
    }
	
	public function fetch_all($query)
    {
        $list = array();
		while($rs = $this->fetch($query)){
			$list[] = $rs;
		}
		return $list;
    }
	
	public function insert_id()
    {
        return mysql_insert_id($this->conn);
    }
	
	public function affected_rows($query)
    {
        return mysql_affected_rows($this->conn);
    }
	
	public function close()
    {
        return mysql_close($this->conn);
    }
	
	public function error()
    {
        return 'Mysql error: '.mysql_error($this->conn);
    }
}
?>