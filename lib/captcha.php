<?php
/*
*	@captcha.php
*	Copyright (c)2013 Mallmold Ecommerce(HK) Limited. 
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

class captcha
{
	private $width = 80;
    private $height = 18;
	
	public function __construct($setting = array())
    {
		$this->set($setting);
	}
	
	public function set($setting)
    {
		$setting['width'] && $this->width = $setting['width'];
		$setting['height'] && $this->height = $setting['height'];
		return $this;
	}
	
	public function set_captcha()
	{
		$str = "ABCDEFGHIJKMNOPQRSTUVWXYZabcdefghkmnopqrstuvwsyz123456789";
		$len = rand(4, 6);
		$n = strlen($str);
		$code = '';
		for($i=0; $i<$len; $i++){
			$k = rand(0, $n-1);
			$code .= $str[$k];
		}
		$_SESSION['captcha_code'] = $code;
		return $code;
	}
	
	public function getcode()
	{
		return $_SESSION['captcha_code'];
	}
	
	public function putimg($code)
	{
		$img = imagecreatetruecolor($this->width, $this->height);
		$background = imagecolorallocate($img, 255, 255, 255);
		imagefill($img, 0, 0, $background);
		$color = imagecolorallocate($img, 0, 0, 0);
		
		$n = rand(5, 10);
		for($i=0; $i<$n; $i++){
			$x = rand(0, $this->width - 6);
			$y = rand(0, $this->height - 6);
			for($j=0; $j<12; $j++){
				imagesetpixel($img, $x + rand(0, 1), $y + rand(0, 1), $color);
			}
		}
		
		$n = rand(0, 1);
		for($i=0; $i<$n; $i++){
			$cx = rand(5, $this->width);
			$cy = rand(0, 2)*$i;
			$w = rand($this->width, $this->width*3);
			$h = rand(floor($this->height/2), $this->height);
			imagearc($img, $cx, $cy, $w, $h, 90, 270, $color);
		}
		
		$len = strlen($code);
		for($i=0; $i<$len; $i++){
			$size = rand(8, 15);
			$x = floor($this->width/$len)*$i + rand(2,6);
            $y = rand(1, $this->height - 16);
            imagechar($img, $size, $x, $y, $code[$i], $color);
		}
		
		$n = rand(1, 3);
		switch($n){
			case 1:
				header('Content-type:image/jpeg');
				imagejpeg($img);
				break;
			case 2:
				header('Content-type:image/gif');
				imagegif($img);
				break;
			case 3:
				header('Content-type:image/png');
				imagepng($img);
				break;
		}
		imagedestroy($img);
	}
}
?>
