<?php
/*
*	@cron.php
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

ignore_user_abort(true);
define('APP_NAME', 'app');
define('BASE_PATH', str_replace('\\','/',dirname(__FILE__)));

$_GET['c'] = 'event';
$_GET['a'] = 'scheduled';

require(BASE_PATH .'/framework/run.php');
?>