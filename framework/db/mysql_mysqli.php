<?php
/*
*	@mysql_mysqli.php
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

class mysql_mysqli
{
	private $conn;
	
	public function connect($host, $user, $pwd, $dbname, $charset = 'utf8')
	{
		$this->conn = mysqli_connect($host, $user, $pwd, $dbname) or exit("Can't connect to MySQL Server");
		$this->query("set names '$charset'");
		return $this->conn;
	}
	
	public function query($sql)
	{
		$query = mysqli_query($this->conn, $sql);
		if(!$query){
			error($this->error());
		}
		return $query;
	}
	
	public function fetch($query)
    {
        return mysqli_fetch_assoc($query);
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
        return mysqli_insert_id($this->conn);
    }
	
	public function affected_rows($query)
    {
        return mysqli_affected_rows($this->conn);
    }
	
	public function close()
    {
        return mysqli_close($this->conn);
    }
	
	public function error()
    {
        return 'Mysql error: '.mysqli_error($this->conn);
    }
}
?>