<?php
/*
*	@errorAction.php
*	Copyright (c)2013-2016 Mallmold.com
*	
*	This program is free software; you can redistribute it and/or
*	modify it under the terms of the GNU General Public License
*	as published by the Free Software Foundation; either version 2
*	of the License, or (at your option) any later version.
*	More details please see: http://www.gnu.org/licenses/gpl.html
*/

require_once(Action('common'));

class errorAction extends commonAction
{
	public function __404($msg = '')
	{
		header("HTTP/1.1 404 Not Found");
		$this->view['msg'] = $msg;
		$this->view('error/404.html');
	}
}

?>