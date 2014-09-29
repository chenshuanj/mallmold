<?php
return array(
	//base
	'SHOW_ERROR' => 1, //debug: 0 or 1
	'TIME_LIMIT' => 0, //run time limit: seconds, 0 is unlimited
	'TIMEZONE' => 'UTC', //time zone code
	
	//database
	'DB_HOST' => 'localhost', //mysql server address
	'DB_PORT' => '3306', //mysql server port
	'DB_NAME' => 'mallmore', //database name
	'DB_USER' => 'root', //database username
	'DB_PSWD' => 'rootpswd', //database password
	'DB_DRIVER' => 'mysql', //mysql or mysqli or pdo_mysql
	'DB_PREFIX' => 'mm_', //table prefix
	
	//control
	'TPL_NAME' => 'default', //default template name
	'TPL_CACHE' => true, //enable template cache or not
	'DATA_CACHE' => true, //enable data cache or not
	'CACHE_TIME' => 3600, //data cache expiration time(seconds)
	'LAN_NAME' => 'en', //default language code
);
?>