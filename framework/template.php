<?php


class template
{
	public $tpl_dir;
	public $tpl_path;
	public $tpl_default;
	public $tpl_name;
	public $cache = 0;
	public $cache_path;
	
	public function parse($str)
	{
		$str = str_replace('{TPL_PATH}', $this->tpl_path, $str);
		$str = preg_replace("/\{include\s+(.+?)\}/ies", "\$this->build('\\1');", $str);		
		$str = preg_replace("/\{if\s+(.+?)\}/is", "<?php if(\\1) { ?>", $str);
		$str = preg_replace("/\{elseif\s+(.+?)\}/is", "<?php } elseif(\\1) { ?>", $str);
		$str = preg_replace("/\{else\}/i", "<?php } else { ?>", $str);
		$str = preg_replace("/\{\/if\}/i", "<?php } ?>", $str);
		$str = preg_replace("/\{foreach\s+(.+?)\}/is", "<?php foreach(\\1) { ?>", $str);
		$str = preg_replace("/\{\/foreach\}/i", "<?php } ?>", $str);
		$str = preg_replace("/\{eval\s+(.+?)\}/is", "<?php \\1 ?>", $str);
		$str = preg_replace("/\{\\$(.+?)\}/i", "<?php echo $\\1; ?>", $str);
		$str = preg_replace("/\{L:(.+?)\}/is", '<?php echo lang("\\1"); ?>', $str);
		$str = preg_replace("/\{:(.+?)\}/is", "<?php echo \\1; ?>", $str);
		return $str;
	}
	
	public function build($path, $include=1)
	{
		$path = trim($path);
		
		if(!$path){
			return null;
		}
		
		if(!$path || $path=='/' || $path=='.'){
			exit('Wrong template name: '. $path);
		}
		
		$isvar = 0;
		if(in_array(substr($path, 0, 1), array('$', '"', '\''))){
			$isvar = 1;
		}
		
		$cachefile = $this->cache_path.$path.'.php';
		if(($this->cache != 1 || !file_exists($cachefile)) && $isvar == 0){
			$tplfile = $this->tpl_dir.$this->tpl_name.'/'.$path;
			if(!file_exists($tplfile)){
				$tplfile = $this->tpl_dir.$this->tpl_default.'/'.$path;
			}
			if(!file_exists($tplfile)){
				exit('Can not find the template file: '.$path);
			}else{
				$str = file_get_contents($tplfile);
				$str = $this->parse($str);
				
				$dir = $this->cache_path . preg_replace('/\/([^\/]*?)$/i', '', $path);
				!is_dir($dir) && mkdir($dir, 0777, true);
				
				file_put_contents($cachefile, $str);
			}
		}
		
		if($include == 1){
			if($isvar == 0){
				return file_get_contents($cachefile);
			}else{
				return '<?php if('.$path.'){ include $this->tpl->build('.$path.', 0); } ?>';
			}
		}else{
			return $cachefile;
		}
	}
}
?>
