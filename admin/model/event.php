<?php


class event extends model
{
	public function add($action, $id)
	{
		$setting = &$this->model('common')->setting();
		$key = md5($id.$setting['smtp_pswd']);
		$do = "$action-$id-$key";
		$this->push($do);
		return null;
	}
	
	private function push($do)
	{
		$lang = cookie('admin_lang');
		$setting = &$this->model('common')->setting();
		$frontend = $setting['frontend'] ? $setting['frontend'] : 'app';
		$router = include BASE_PATH .'/'.$frontend.'/router.php';
		if(isset($router['type']) && $router['type'] > 0){
			$path = '/index.php/event/index/lang/'.$lang.'/do/'.$do;
		}else{
			$path = '/index.php?c=event&a=index&lang='.$lang.'&do='.$do;
		}
		$host = $_SERVER['HTTP_HOST'];
		
		$header = "GET $path HTTP/1.0\r\n";
		$header .= "Host: $host\r\n";
		$header .= "Accept: */*\r\n";
		$header .= "Connection: Close\r\n";
		$header .= "\r\n";
		
		$fp = fsockopen($host, 80, $errno, $errstr, 5);
		if($fp){
			stream_set_blocking($fp, 0);
			stream_set_timeout($fp, 3);
			fwrite($fp, $header);
			fclose($fp);
		}else{
			$this->model('report')->add('event', 'fsockopen: '.$errstr, 0);
		}
	}
}
?>