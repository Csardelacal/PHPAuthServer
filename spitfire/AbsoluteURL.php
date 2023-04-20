<?php

trigger_error('Using deprecated \AbsoluteURL class', E_USER_DEPRECATED);

/**
 * This class just mimics the previous behavior of URL so that legacy code works
 * just raising a deprecation notice.
 * 
 * To use the absoluteURL class you should use <code>url(*params*)->absolute()</code>
 * 
 * @see \url()
 * @see spitfire\core\http\URL::absolute()
 * @deprecated since version 0.1-dev 20170502
 */
class AbsoluteURL extends spitfire\core\http\AbsoluteURL
{
	
	public function __construct() {

		#Get the parameters the first time
		$sf     = spitfire();
		$params = func_get_args();

		#Extract the app
		if (reset($params) instanceof App || $sf->appExists(reset($params))) {
			$app = array_shift($params);
		}
		else {
			$app = $sf;
		}

		#Get the controller, and the action
		$controller = null;
		$action     = null;
		$object     = Array();

		#Get the object
		while(!empty($params) && !is_array(reset($params)) ) {
			if     (!$controller) { $controller = array_shift($params); }
			elseif (!$action)     { $action     = array_shift($params); }
			else                  { $object[]   = array_shift($params); }
		}

		#Get potential environment variables that can be used for additional information
		#like loccalization
		$get          = array_shift($params);
		$environment  = array_shift($params);

		parent::__construct($app, $controller, $action, $object, 'php', $get, $environment);
	}
	
}
