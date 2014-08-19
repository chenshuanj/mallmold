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
			$list = $this->db->table('setting')->getlist();
			foreach($list as $v){
				$setting[$v['name']] = $v['val'];
			}
		}
		return $setting;
	}
	
	public function &languages()
	{
		static $languages=array();
		if(!$languages){
			$languages = $this->db->table('language')->where('status=1')->getlist();
		}
		return $languages;
	}
	
	public function &currencies()
	{
		static $currencies;
		if(!$currencies){
			$currencies = $this->model('mdata')->table('currency')->where('status=1')->getlist();
		}
		return $currencies;
	}
	
	public function main_lang()
	{
		$setting = &$this->setting();
		return $setting['default_lang'];
	}
	
	public function main_cur()
	{
		$setting = &$this->setting();
		return $setting['default_cur'];
	}
	
	public function weight_unit()
	{
		$setting = &$this->setting();
		return $setting['weight_unit'];
	}
	
	public function &current_symbol()
	{
		static $current_symbol;
		if(!$current_symbol){
			$current_cur = $this->main_cur();
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
}
?>