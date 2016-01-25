<?php
/*
*	@dictionary.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

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
	
	private function getval($type, $sign, $language = '')
	{
		if(!$language){
			$language = $this->current_lang;
		}
		
		//read from cache
		if($this->model('cache')->enable == true){
			$value = $this->model('cache')->get($language, $sign);
			if($value !== false){
				return $value;
			}
		}
		
		if($type == '_key_')
		{
			$field = 'dict_val_'.$language;
			$value = $this->db->table('dict')->where("dict_key='$sign'")->getval($field);
			if(!$value && $language != $this->main_lang){
				$field = 'dict_val_'.$this->main_lang;
				$value = $this->db->table('dict')->where("dict_key='$sign'")->getval($field);
			}
		}
		elseif($type == '_txtkey_')
		{
			$table = 'dict_text_'.$language;
			$value = $this->db->table($table)->where("text_key='$sign'")->getval('content');
			if(!$value && $language != $this->main_lang){
				$table = 'dict_text_'.$this->main_lang;
				$value = $this->db->table($table)->where("text_key='$sign'")->getval('content');
			}
		}
		
		//write to cache
		if($this->model('cache')->enable == true){
			$this->model('cache')->set($language, $sign, $value);
		}
		
		return $value;
	}
	
	public function getdict($data = array(), $language = '')
	{
		if(!is_array($data)){
			return null;
		}
		
		foreach($data as $key=>$val){
			if(!is_array($val)){
				$keytype = $this->checkkey($key);
				if($keytype){
					$newkey = str_replace($keytype, '', $key);
					$data[$newkey] = $this->getval($keytype, $val, $language);
				}
			}else{
				$data[$key] = $this->getdict($val);
			}
		}
		return $data;
	}
}
?>