<?php
/*
*	@csv.php
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

class csv
{
	public function get($file)
	{
		$fp = fopen($file, 'r');
		$head = fgetcsv($fp);
		$data = array();
		while($csv = fgetcsv($fp)){
			$row = array();
			foreach($head as $k=>$v){
				$row[$v] = $csv[$k];
			}
			$data[] = $row;
		}
		fclose($fp);
		return $data;
	}
	
	public function put($data)
	{
		$str = array();
		foreach($data as $row){
			$line = array();
			foreach($row as $v){
				$line[] = str_replace('"', '""', $v);
			}
			$str[] = '"'.implode('","', $line).'"';
		}
		
		$str = implode("\r\n", $str);
		return $str;
	}
}
?>