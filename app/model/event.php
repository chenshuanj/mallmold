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
	
	public function get($do)
	{
		if(!$do){
			return false;
		}
		$arr = explode('-', $do);
		if(count($arr) == 3){
			$setting = &$this->model('common')->setting();
			if($arr[1] && $arr[2] == md5($arr[1].$setting['smtp_pswd'])){
				return array(
					'action' => $arr[0],
					'id' => $arr[1],
				);
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	private function push($do)
	{
		$lang = cookie('lang');
		$path = url('event/index?lang='.$lang.'&do='.$do);
		
		$uri = parse_url($path);
		$path = $uri['path'].($uri['query'] ? '?'.$uri['query'] : '');
		$host = $uri['host'];
		
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