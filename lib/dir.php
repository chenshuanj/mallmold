<?php


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