<?php


class cache extends model
{
	public $enable = false;
	public $type = '';
	private $cache;
	
	public function __construct()
    {
		parent::__construct();
		
		$setting = &$this->model('common')->setting();
		if(in_array($setting['dict_cache'], array('redis', 'memcache'))){
			$this->type = $setting['dict_cache'];
			$cache_set = array(
				'host' => $setting[$this->type.'_host'],
				'port' => $setting[$this->type.'_port'],
			);
			$this->cache = $this->load('lib/cache_'.$this->type, $cache_set);
			if(!$this->cache->is_install()){
				$this->error_report();
			}else{
				$status = $this->cache->connect();
				if(!$status){
					$this->error_report();
					return false;
				}
				$status = $this->cache->auth($setting[$this->type.'_pswd']);
				if(!$status){
					$this->error_report();
					return false;
				}
				$this->enable = true;
			}
		}
	}
	
	public function getdb($lang_code)
    {
		$default_codes = $this->model('dict')->default_codes;
		return array_search($lang_code, $default_codes);
	}
	
	public function set($lang_code, $key, $value)
    {
		$db = $this->getdb($lang_code);
		$this->cache->select($db);
		return $this->cache->set($key, $value);
	}
	
	public function get($lang_code, $key)
    {
		$db = $this->getdb($lang_code);
		$this->cache->select($db);
		return $this->cache->get($key);
	}
	
	private function error_report()
	{
		//disable cache
		$this->db->table('setting')->where("name='dict_cache'")->update(array('val' => ''));
		//reset setting cache
		$this->cache('setting', 0);
		
		$message = $this->type.': '.addslashes($this->cache->error);
		$this->model('report')->add('cache', $message);
		return null;
	}
}
?>