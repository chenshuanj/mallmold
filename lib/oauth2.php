<?php

class oauth2{
	public function __construct() {
		spl_autoload_register('oauth2::autoload');
	}
	
    public static function getInstance($config, $type, $token = null) {
    	$name = ucfirst(strtolower($type)) . 'SDK';
    	if (class_exists($name)) {
    		return new $name($config, $token);
    	} else {
    		halt(L('_CLASS_NOT_EXIST_') . ':' . $name);
    	}
    }
    
    public static function autoload($className) {
    	require_once "oauth2/{$className}.class.php";
    }
}