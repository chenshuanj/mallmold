<?php


class dictionary extends model
{
	private $main_lang = '';
	public $current_lang = '';
	
	public function __construct()
    {
		parent::__construct();
		
		$this->main_lang = &$this->model('common')->main_lang();
		
		$current_lang = cookie('lang');
		!$current_lang && $current_lang = $this->main_lang;
		
		$this->current_lang = $current_lang;
	}
	
	private function checkkey($key)
	{
		if(!$key)
			return null;
		
		if(strpos($key, '_key_') > 0)
		{
			return '_key_';
		}
		elseif(strpos($key, '_txtkey_') > 0)
		{
			return '_txtkey_';
		}
		else
		{
			return null;
		}
	}
	
	private function getval($type, $sign)
	{
		//read from cache
		if($this->model('cache')->enable == true){
			$value = $this->model('cache')->get($this->current_lang, $sign);
			if($value !== false){
				return $value;
			}
		}
		
		if($type == '_key_')
		{
			$field = 'dict_val_'.$this->current_lang;
			$value = $this->db->table('dict')->where("dict_key='$sign'")->getval($field);
			if(!$value && $this->current_lang != $this->main_lang){
				$field = 'dict_val_'.$this->main_lang;
				$value = $this->db->table('dict')->where("dict_key='$sign'")->getval($field);
			}
		}
		elseif($type == '_txtkey_')
		{
			$table = 'dict_text_'.$this->current_lang;
			$value = $this->db->table($table)->where("text_key='$sign'")->getval('content');
			if(!$value && $this->current_lang != $this->main_lang){
				$table = 'dict_text_'.$this->main_lang;
				$value = $this->db->table($table)->where("text_key='$sign'")->getval('content');
			}
		}
		
		//write to cache
		if($this->model('cache')->enable == true){
			$this->model('cache')->set($this->current_lang, $sign, $value);
		}
		
		return $value;
	}
	
	public function getdict($data = array())
	{
		if(!is_array($data)){
			return null;
		}
		
		foreach($data as $key=>$val){
			if(!is_array($val)){
				$keytype = $this->checkkey($key);
				if($keytype){
					$newkey = str_replace($keytype, '', $key);
					$data[$newkey] = $this->getval($keytype, $val);
				}
			}else{
				$data[$key] = $this->getdict($val);
			}
		}
		return $data;
	}
}
?>