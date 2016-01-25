<?php
/*
*	@cache_redis.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

class cache_redis
{
	public $host = '127.0.0.1';
	public $port = 6379;
	public $timeout = 1;
	public $error;
	private $redis;
	
	public function __construct($setting = array())
    {
		$this->setting($setting);
	}
	
	public function setting($setting)
    {
		$setting['host'] && $this->host = $setting['host'];
		$setting['port'] && $this->port = $setting['port'];
		$setting['timeout'] && $this->timeout = $setting['timeout'];
		return $this;
	}
	
	public function is_install()
    {
		$status = class_exists('Redis');
		if(!$status){
			$this->error = 'Redis extension not exists';
		}
		return $status;
	}
	
	public function connect()
    {
		if(!$this->host || !$this->port){
			$this->error = 'Host or port is not set';
			return false;
		}
		
		$this->redis = new Redis();
		$status = true;
		try{
			$status = $this->redis->connect($this->host, $this->port, $this->timeout);
		}catch(Exception $e){
			$status = false;
			$this->error = $e->getMessage();
		}
		return $status;
	}
	
	public function auth($pswd)
    {
		$status = $this->redis->auth($pswd);
		if(!$status){
			$this->error = 'auth error';
		}
		return $status;
	}
	
	public function select($db)
    {
		return $this->redis->select($db);
	}
	
	public function exists($key)
    {
		return $this->redis->exists($key);
	}
	
	public function set($key, $value)
    {
		return $this->redis->set($key, $value);
	}
	
	public function get($key)
    {
		return $this->redis->get($key);
	}
	
	public function delete($key)
    {
		return $this->redis->delete($key);
	}
	
	public function flush()
    {
		return $this->redis->flushAll();
	}
	
	public function close()
    {
		return true;
	}
}
?>