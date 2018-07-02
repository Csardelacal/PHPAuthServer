<?php namespace settings;

use spitfire\exceptions\PrivateException;

class DefaultSettings
{
	
	static private $defaults = Array(
		 
		 //PAGE SETTINGS
		'page.logo' => 'img/logo-default.png',
		 
		 //Email Settings
		'smtp.from' => 'admin@yourserver.com',
		 
		 //Webhook settings
		'cptn.h00k' => null
	);
	
	public static function get($key) {
		#If there is an already available default setting we grab it.
		if (array_key_exists($key, self::$defaults)) { return self::$defaults[$key]; }
		#Otherwise we crash the app
		throw new PrivateException('You attempted reading ' . $key .'\'s default value, which was not defined', 1603072206);
	}
}
