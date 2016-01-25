<?php
/*
*	@visitor.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

class visitor extends model
{
	public function &visitor_set()
	{
		static $set = null;
		if($set == null){
			$host = $_SERVER['HTTP_HOST'];
			$set = $this->db->table('host')->where("host='$host'")->get();
		}
		return $set;
	}
	
	public function visitor_tpl()
	{
		$template = null; 
		$set = &$this->visitor_set();
		if(!empty($set['template'])){
			$template = $set['template'];
		}else{
			$setting = &$this->model('common')->setting();
			if($setting['mobile_mode'] == 1 && $this->is_mobile() && !empty($setting['mobile_tpl'])){
				$template = $setting['mobile_tpl'];
			}elseif(!empty($setting['default_tpl'])){
				$template = $setting['default_tpl'];
			}
		}
		return $template;
	}
	
	public function visitor_country()
	{
		$country_id = 0;
		$set = &$this->visitor_set();
		if($set['bind_country']){
			$country_id = $set['bind_country'];
		}else{
			$setting = &$this->model('common')->setting();
			if(!empty($setting['default_country'])){
				$country_id = $setting['default_country'];
			}
		}
		return $country_id;
	}
	
	public function visitor_lang()
	{
		if($_GET['set_lang']){
			$code = strtolower(trim($_GET['set_lang']));
			if($this->check_lang($code)){
				return $code;
			}
		}
		
		$code = cookie('lang');
		if($code && $this->check_lang($code)){
			return $code;
		}
		
		$setting = &$this->model('common')->setting();
		if($setting['use_agent_lang'] == 1){
			$code = $this->get_ualang();
			if($this->check_lang($code)){
				return $code;
			}
		}
		
		$set = &$this->visitor_set();
		if(!empty($set['bind_language'])){
			return $set['bind_language'];
		}
		
		$code = &$this->model('common')->main_lang();
		return $code;
	}
	
	public function visitor_currency()
	{
		if($_GET['set_cur']){
			$code = trim($_GET['set_cur']);
			if($this->check_cur($code)){
				return $code;
			}
		}
		
		$code = cookie('cur');
		if($code && $this->check_cur($code)){
			return $code;
		}
		
		$set = &$this->visitor_set();
		if(!empty($set['bind_currency'])){
			return $set['bind_currency'];
		}
		
		$code = &$this->model('common')->main_cur();
		return $code;
	}
	
	public function check_lang($code)
	{
		$languages = &$this->model('common')->languages();
		foreach($languages as $v){
			if($code == $v['code']){
				return true;
			}
		}
		return false;
	}
	
	public function check_cur($code)
	{
		$currencies = &$this->model('common')->currencies();
		foreach($currencies as $v){
			if(strtoupper($code) == strtoupper($v['code'])){
				return true;
			}
		}
		return false;
	}
	
	public function is_spider()
	{
		static $is_spider = null;
		if($is_spider == null){
			$is_spider = false;
			$setting = &$this->model('common')->setting();
			if(!$setting['spider_code']){
				return $is_spider;
			}
			
			$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
			$bots = explode('|', $setting['spider_code']);
			foreach($bots as $bot){
				if(strpos($ua, strtolower($bot)) !== false){
					$is_spider = true;
					return $is_spider;
				}
			}
		}
		return $is_spider;
	}
	
	public function is_mobile()
	{
		static $is_mobile = null;
		if($is_mobile == null){
			$is_mobile = false;
			$setting = &$this->model('common')->setting();
			if(!$setting['mobile_mode']){
				return $is_mobile;
			}
			
			$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
			$keyswords = explode('|', $setting['mobile_ua']);
			foreach($keyswords as $code){
				if(strpos($ua, strtolower($code)) !== false){
					$is_mobile = true;
					return $is_mobile;
				}
			}
		}
		return $is_mobile;
	}
	
	public function get_ualang()
	{
		$accept = str_replace(';', ',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$arr = explode(',', $accept, 2);
		$arr[0] = trim($arr[0]);
		if(strlen($arr[0]) > 2){
			$lang = strtolower($arr[0]);
			$code = substr($lang, 0, 2);
			if($code == 'zh'){
				return str_replace('-', '_', $lang);
			}else{
				return $code;
			}
		}else{
			return strtolower($arr[0]);
		}
	}
	
	public function get_ip()
	{
		return $_SERVER['REMOTE_ADDR'];
	}
}
?>