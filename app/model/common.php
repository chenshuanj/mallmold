<?php
/*
*	@common.php
*	Copyright (c)2013-2014 Mallmold Ecommerce(HK) Limited. 
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

class common extends model
{
	public function &setting()
	{
		static $setting=array();
		if(!$setting){
			$setting = $this->cache('setting');
			if(!$setting){
				$setting=array();
				$list = $this->db->table('setting')->getlist();
				foreach($list as $v){
					$setting[$v['name']] = $v['val'];
				}
				$this->cache('setting', $setting);
			}
		}
		return $setting;
	}
	
	public function &languages()
	{
		static $languages=array();
		if(!$languages){
			$languages = $this->cache('languages', null, 0);
			if(!$languages){
				$languages = $this->db->table('language')->where('status=1')->getlist();
				$this->cache('languages', $languages, 0);
			}
		}
		return $languages;
	}
	
	public function &currencies()
	{
		static $currencies;
		if(!$currencies){
			$currencies = $this->cache('currencies', null, 0);
			if(!$currencies){
				$currencies = $this->model('mdata')->table('currency')->where('status=1')->getlist();
				$this->cache('currencies', $currencies, 0);
			}
		}
		return $currencies;
	}
	
	public function &main_lang()
	{
		static $main_lang;
		if(!$main_lang){
			$setting = &$this->setting();
			$main_lang = $setting['default_lang'];
		}
		return $main_lang;
	}
	
	public function &main_cur()
	{
		static $main_cur;
		if(!$main_cur){
			$setting = &$this->setting();
			$main_cur = $setting['default_cur'];
		}
		return $main_cur;
	}
	
	public function current_cur()
	{
		return cookie('cur');
	}
	
	public function &current_symbol()
	{
		static $current_symbol;
		if(!$current_symbol){
			$current_cur = $this->current_cur();
			$currencies = &$this->currencies();
			foreach($currencies as $v){
				if($v['code'] == $current_cur){
					$current_symbol = $v['symbol'];
					break;
				}
			}
		}
		return $current_symbol;
	}
	
	public function current_price($price, $prefix=1)
	{
		$main_cur = &$this->main_cur();
		$current_cur = cookie('cur');
		$symbol = &$this->current_symbol();
		$rate = 1;
		if($current_cur != $main_cur){
			$currencies = &$this->currencies();
			foreach($currencies as $v){
				if($v['code'] == $current_cur){
					$symbol = $v['symbol'];
					$rate = $v['rate'];
					break;
				}
			}
		}
		return ($prefix==1 ? $symbol : '').round($price*$rate, 2);
	}
	
	public function date_format($time)
	{
		static $date_format;
		if(!$date_format){
			$setting = &$this->setting();
			$date_format = $setting['date_format'] ? $setting['date_format'] : 'Y-m-d H:i:s';
		}
		return date($date_format, $time);
	}
	
	public function nav($type)
	{
		$list = $this->cache('nav_'.$type);
		if(!$list){
			$list = $this->model('mdata')->table('nav')->where("type=$type and status=1")->getlist();
			$this->cache('nav_'.$type, $list);
		}
		return $list;
	}
	
	public function block($code)
	{
		$content = $this->cache('block_'.$code);
		if(!$content){
			$row = $this->model('mdata')->table('block')->where("code='$code'")->get();
			$content = $row['content'];
			$this->cache('block_'.$code, $content);
		}
		return $content;
	}
	
	public function back_url()
	{
		return $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : url('index/index');
	}
}
?>