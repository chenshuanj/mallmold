<?php


class http
{
	public $type = 'socket';
	public $error;
	
	public function get($url)
    {
		$fun = 'http_'.$this->type;
		return $this->$fun($url);
	}
	
	public function post($url, $content)
    {
		$fun = 'http_'.$this->type;
		return $this->$fun($url, 'POST', $content);
	}
	
	public function http_file_get($url, $timeout=10)
    {
		$opts = array(
			'http'=>array(
				'method' => 'GET',
				'timeout' => $timeout,
			)
		);
		$context = stream_context_create($opts);
		return @file_get_contents($url, false, $context);
	}
	
	public function http_socket($url, $method='GET', $content='')
    {
		$uri = parse_url($url);
		$scheme = $uri['scheme'] != 'https' ? '' : 'ssl://';
		$port = $uri['scheme'] != 'https' ? 80 : 443;
		$path = $uri['path'].($uri['query'] ? '?'.$uri['query'] : '');
		$host = $uri['host'];
		
		$header = "$method $path HTTP/1.1\r\n";
		$header .= "Host: $host\r\n";
		$header .= "Content-Type: text/xml\r\n";
		if($method == 'POST'){
			$header .= "Content-Length: ".strlen($content)."\r\n";
		}
		$header .= "Connection: close\r\n\r\n";
		
		$fp = fsockopen($scheme.$host, $port, $errno, $errstr, 30);
		if(!$fp){
			$this->error = 'fsockopen: '.$errstr;
			return false;
		}else{
			fputs($fp, $header.$content);
			$res = '';
			while(!feof($fp)){
				$res .= fgets($fp, 1024);
			}
			fclose($fp);
			$rs = explode("\r\n\r\n", $res, 2);
			return $rs[1];
		}
	}
	
	public function http_curl($url, $method='GET', $content='')
    {
		$request = curl_init($url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_TIMEOUT, 30);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		if($method == 'POST'){
			curl_setopt($request, CURLOPT_POSTFIELDS, $content);
		}
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response = curl_exec($request);
		curl_close($request);
		
		return $response;
	}
}
?>