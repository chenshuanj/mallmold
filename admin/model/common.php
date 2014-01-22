<?php


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