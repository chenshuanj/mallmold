<?php


class cache_memcache
{
	public $host = '127.0.0.1';
	public $port = 11211;
	public $timeout = 1;
	public $error;
	private $memcache;
	private $db = '';
	
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
		$status = class_exists('Memcache');
		if(!$status){
			$this->error = 'Memcache extension not exists';
		}
		return $status;
	}
	
	public function connect()
    {
		if(!$this->host || !$this->port){
			$this->error = 'Host or port is not set';
			return false;
		}
		
		$this->memcache = new Memcache;
		$status = @$this->memcache->connect($this->host, $this->port, $this->timeout);
		if(!$status){
			$this->error = "Can't connect to ".$this->host.":".$this->port;
		}
		return $status;
	}
	
	public function auth($pswd='')
    {
		return true;
	}
	
	public function select($db='')
    {
		$this->db = !empty($db) ? $db.'_' : '';
		return true;
	}
	
	public function exists($key)
    {
		return $this->get($key) ? true : false;
	}
	
	public function set($key, $value)
    {
		if($this->exists($key)){
			return $this->memcache->replace($this->db.$key, $value);
		}else{
			return $this->memcache->set($this->db.$key, $value, MEMCACHE_COMPRESSED, 0);
		}
	}
	
	public function get($key)
    {
		return $this->memcache->get($this->db.$key);
	}
	
	public function delete($key)
    {
		return $this->memcache->delete($this->db.$key);
	}
	
	public function flush()
    {
		return $this->memcache->flush();
	}
	
	public function close()
    {
		return $this->memcache->close();
	}
}
?>