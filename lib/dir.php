<?php
/*
*	@dir.php
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

class dir
{
	public function checkdir($file)
    {
		$dir = pathinfo($file, PATHINFO_DIRNAME);
		$dir = str_replace('\\', '/', $dir);
		$paths = explode('/', $dir);
		$path = '';
		foreach($paths as $k=>$node){
			if($k == 0){
				$path .= $node;
				continue;
			}
			$path .= '/'.$node;
			if(!is_dir($path)){
				mkdir($path);
			}
		}
		return $path;
	}
	
	public function deldir($dir)
    {
		if(!is_dir($dir)){
			return false;
		}
		
		$hd = opendir($dir);
		while($f = readdir($hd)){
			if($f == '.' || $f == '..'){
				continue;
			}
			$path = $dir.'/'.$f;
			if(is_dir($path)){
				$this->deldir($path);
			}else{
				unlink($path);
			}
		}
		closedir($hd);
		return rmdir($dir);
	}
	
}
?>