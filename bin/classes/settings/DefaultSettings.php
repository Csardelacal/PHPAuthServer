<?php namespace settings;

use spitfire\exceptions\PrivateException;

class DefaultSettings
{
	
	static private $defaults = Array(
		 
		 //PAGE SETTINGS
		'page.logo' => 'img/logo-default.png',
		 
		 //Email Settings
		'smtp.from' => 'admin@yourserver.com'
	);
	
	public static function get($key) {
		#If there is an already available default setting we grab it.
		if (isset(self::$defaults[$key])) { return self::$defaults[$key]; }
		#Otherwise we crash the app
		throw new PrivateException('You attempted reading ' . $key .'\'s default value, which was not defined', 201603072206);
	}
}
