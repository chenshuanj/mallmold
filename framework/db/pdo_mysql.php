<?php
/*
*	@pdo_mysql.php
*	Copyright (c)2013-2014 Mallmold Ecommerce(HK) Limited.
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

class pdo_mysql
{
	private $conn;
	
	public function connect($host, $user, $pwd, $dbname, $charset = 'utf8')
	{
		try{
			$options = array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "set names '$charset'",
				PDO::ATTR_PERSISTENT => true,
			);
			$this->conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pwd, $options);
			$this->conn->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
		}catch(PDOException $e){
			exit($e->getMessage());
		}
		
		return $this->conn;
	}
	
	public function query($sql)
	{
		$query = $this->conn->query($sql);
		if(!$query){
			error($this->error());
		}
		return $query;
	}
	
	public function fetch($query)
    {
        return $query->fetch(PDO::FETCH_ASSOC);
    }
	
	public function fetch_all($query)
    {
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
	
	public function insert_id()
    {
        return $this->conn->lastInsertId();
    }
	
	public function affected_rows($query)
    {
        return $query->rowCount();
    }
	
	public function close()
    {
        $this->conn = null;
    }
	
	public function error()
    {
        return 'Mysql error: '.$this->conn->errorInfo();
    }
}
?>