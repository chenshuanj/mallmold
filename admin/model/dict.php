<?php
/*
*	@dict.php
*	Copyright (c)2013 Mallmold Ecommerce(HK) Limited. 
*	http://www.mallmold.com/
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*	
*	If you want to get an unlimited version of the program or want to obtain
*	additional services, please send an email to <service@mallmold.com>.
*/

class dict extends model
{
	public $lang_code = '';
	public $default_codes = array();
	
	public function __construct()
    {
		parent::__construct();
		
		$this->lang_code = cookie('admin_lang');
		
		$langs = array();
		$list = &$this->model('common')->languages();
		foreach($list as $v){
			$langs[] = $v['code'];
		}
		$this->default_codes = $langs;
	}
	
	protected function checkkey($key)
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
	
	protected function createsign()
	{
		$keyid = $this->db->table('dict_keys')->insert(array());
		return 'k_'.$keyid;
	}
	
	protected function getval($type, $sign, $lang_code)
	{
		if($type == '_key_')
		{
			$field = 'dict_val_'.$lang_code;
			return $this->db->table('dict')->where("dict_key='$sign'")->getval($field);
		}
		elseif($type == '_txtkey_')
		{
			$table = 'dict_text_'.$lang_code;
			return $this->db->table($table)->where("text_key='$sign'")->getval('content');
		}
	}
	
	protected function setval($type, $sign, $value, $lang_code)
	{
		$status = null;
		if($type == '_key_')
		{
			$field = 'dict_val_'.$lang_code;
			$n = $this->db->table('dict')->where("dict_key='$sign'")->count();
			if($n>0){
				$status = $this->db->table('dict')->where("dict_key='$sign'")->update(array($field=>$value));
			}else{
				$status = $this->db->table('dict')->insert(array('dict_key'=>$sign, $field=>$value));
			}
		}
		elseif($type == '_txtkey_')
		{
			$table = 'dict_text_'.$lang_code;
			$n = $this->db->table($table)->where("text_key='$sign'")->count();
			if($n>0){
				$status = $this->db->table($table)->where("text_key='$sign'")->update(array('content'=>$value));
			}else{
				$status = $this->db->table($table)->insert(array('text_key'=>$sign, 'content'=>$value));
			}
		}
		
		//save to cache
		if($this->model('cache')->enable == true){
			$this->model('cache')->set($lang_code, $sign, $value);
		}
		
		return $status;
	}
	
	protected function delval($type, $sign, $lang_codes)
	{
		if($type == '_key_')
		{
			return $this->db->table('dict')->where("dict_key='$sign'")->delete();
		}
		elseif($type == '_txtkey_')
		{
			foreach($lang_codes as $code){
				$table = 'dict_text_'.$code;
				$this->db->table($table)->where("text_key='$sign'")->delete();
			}
			return null;
		}
	}
	
	public function getdict($data = array(), $code = '')
	{
		if(!is_array($data)){
			return null;
		}
		
		if($code == ''){
			$code = $this->lang_code;
		}
		
		foreach($data as $key=>$val){
			if(!is_array($val)){
				$keytype = $this->checkkey($key);
				if($keytype){
					$newkey = str_replace($keytype, '', $key);
					$data[$newkey] = $this->getval($keytype, $val, $code);
				}
			}else{
				$data[$key] = $this->getdict($val, $code);
			}
		}
		return $data;
	}
	
	public function setdict($data = array(), $code = '')
	{
		if(!is_array($data)){
			return null;
		}
		
		if($code == ''){
			$code = $this->lang_code;
		}
		
		foreach($data as $key=>$val){
			if(!is_array($val)){
				$keytype = $this->checkkey($key);
				if($keytype){
					$newkey = str_replace($keytype, '', $key);
					if(isset($data[$newkey])){
						if(!$data[$newkey] && !$data[$key]){
							unset($data[$newkey]);
							continue;
						}
						
						if($val){
							$sign = $val;
						}else{
							$sign = $this->createsign();
							$data[$key] = $sign;
						}
						$s = $this->setval($keytype, $sign, $data[$newkey], $code);
						if(is_null($s)){
							error($this->db->error());
						}
						
						unset($data[$newkey]);
					}
				}
			}else{
				$data[$key] = $this->setdict($val, $code);
			}
		}
		
		return $data;
	}
	
	public function deldict($data, $codes = array())
	{
		if(!is_array($data)){
			return null;
		}
		
		if(!$codes){
			$codes = $this->default_codes;
		}
		
		foreach($data as $key=>$val){
			if(!is_array($val)){
				$keytype = $this->checkkey($key);
				if($keytype){
					$this->delval($keytype, $val, $codes);
				}
			}
		}
	}
	
	public function get_vals($type, $sign)
	{
		$langs = $this->getlangs();
		
		if($type == '_key_'){
			$rs = $this->db->table('dict')->where("dict_key='$sign'")->get();
			foreach($rs as $k=>$v){
				if($k=='id' || $k=='dict_key'){
					continue;
				}
				$code = str_replace('dict_val_', '', $k);
				if(isset($langs[$code])){
					$langs[$code]['value'] = $v;
				}
			}
		}elseif($type == '_txtkey_'){
			foreach($langs as $k=>$v){
				$table = 'dict_text_'.$k;
				$langs[$k]['value'] = $this->db->table($table)->where("text_key='$sign'")->getval('content');
			}
		}
		return $langs;
	}
	
	public function set_vals($type, $sign, $data)
	{
		$langs = $this->getlangs();
		if($type == '_key_'){
			$arr = array();
			foreach($langs as $k=>$v){
				$key = 'dict_val_'.$k;
				$arr[$key] = $data[$k];
			}
			$this->db->table('dict')->where("dict_key='$sign'")->update($arr);
		}elseif($type == '_txtkey_'){
			foreach($langs as $k=>$v){
				$table = 'dict_text_'.$k;
				$n = $this->db->table($table)->where("text_key='$sign'")->count();
				if($n > 0){
					$this->db->table($table)->where("text_key='$sign'")->update(array('content' => $data[$k]));
				}else{
					$this->db->table($table)->insert(array('text_key' => $sign, 'content' => $data[$k]));
				}
			}
		}
		return true;
	}
	
	private function getlangs()
	{
		$langs = array();
		$list = $this->db->table('language')->where('status=1')->getlist();
		foreach($list as $v){
			$langs[$v['code']] = $v;
		}
		return $langs;
	}
}
?>