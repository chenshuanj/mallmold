<?php
/*
*	@image_gd.php
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

class image_gd
{
	private function getimg($img)
	{
		if(!$img || !file_exists($img)){
            return false;
        }
		
		$img_info = getimagesize($img);
		switch($img_info[2]){
            case 1:
				$img_info['type'] = 'gif';
				$img_info['from'] = imagecreatefromgif($img);
				$img_info['creat'] = 'imagegif';
				break;
            case 2:
				$img_info['type'] = 'jpg';
				$img_info['from'] = imagecreatefromjpeg($img);
				$img_info['creat'] = 'imagejpeg';
				break;
            case 3:
				$img_info['type'] = 'png';
				$img_info['from'] = imagecreatefrompng($img);
				$img_info['creat'] = 'imagepng';
				break;
            default:
				return false;
        }
		return $img_info;
	}
	
	function thumbnail($img, $tnpath, $w, $h)
	{
		$org_info = $this->getimg($img);
		if($org_info){
			if (($org_info[0] / $org_info[1]) >= (4 / 3)){
				$y = round($org_info[1] / ($org_info[0] / $w));
				$x = $w;
			}else{
				$x = round($org_info[0] / ($org_info[1] / $h));
				$y = $h;
			}
			
			$sm_image = imagecreatetruecolor($x, $y);
			Imagecopyresampled($sm_image, $org_info['from'], 0, 0, 0, 0, $x, $y, $org_info[0], $org_info[1]);
			$_creatImage = $org_info['creat'];
			if($_creatImage == 'imagejpeg'){
				$thumbnail = @$_creatImage($sm_image, $tnpath, 80);
			}else{
				$thumbnail = @$_creatImage($sm_image, $tnpath);
			}
			imagedestroy ($sm_image);
			return $thumbnail;
		}else{
			return false;
		}
	}
	
    function water_mark($groundImage, $waterImage, $waterPos=3 ,$alpha=80, $xOffset=0, $yOffset=0)
	{
        $water_info = $this->getimg($waterImage);
        if(!$water_info){
			return false;
		}
		$ground_info = $this->getimg($groundImage);
        if(!$ground_info){
			return false;
		}
		
		$ground_w = $ground_info[0];
        $ground_h = $ground_info[1];
        $water_w = $water_info[0];
        $water_h = $water_info[1];
        if(($ground_w < $water_w) || ($ground_h < $water_h)) {
            return false;
        }
        switch($waterPos) {
            case 1:
                $posX = $xOffset;
                $posY = $yOffset;
                break;
            case 2:
                $posX = $ground_w - $water_w - $xOffset;
                $posY = $yOffset;
                break;
            case 3:
				$posX = ($ground_w - $water_w) / 2;
				$posY = ($ground_h - $water_h) / 2;
				break;
            case 4:
                $posX = $xOffset;
                $posY = $ground_h - $water_h - $yOffset;
				break;
            case 5:
				$posX = $ground_w - $water_w - $xOffset;
				$posY = $ground_h - $water_h - $yOffset;
				break;
            default:
				$posX = ($ground_w - $water_w) / 2;
				$posY = ($ground_h - $water_h) / 2;
				break;
        }
		
		imagealphablending($ground_info['from'], true);
        imagecopymerge($ground_info['from'], $water_info['from'], $posX, $posY, 0, 0, $water_w, $water_h, $alpha);
        @unlink($groundImage);
		$_creatImage = $ground_info['creat'];
		$make = $_creatImage($ground_info['from'], $groundImage);
		imagedestroy($water_info['from']);
		imagedestroy($ground_info['from']);
        return $make;
    }
}
?>