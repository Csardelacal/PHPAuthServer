<?php

use spitfire\App;
use spitfire\collection\Collection;
use spitfire\core\ContextInterface;
use spitfire\core\Environment;
use spitfire\core\http\URL;
use spitfire\io\cli\Console;
use spitfire\locale\Domain;
use spitfire\locale\DomainGroup;
use spitfire\locale\Locale;
use spitfire\SpitFire;
use spitfire\SpitFireCLI;
use spitfire\storage\database\DB;
use spitfire\storage\database\Settings;
use spitfire\validation\ValidationException;
use spitfire\validation\Validator;
use spitfire\validation\ValidatorInterface;

/**
 * This is a quick hand method to use Spitfire's main App class as a singleton.
 * It allows you to quickly access many of the components the framework provides
 * to make it easier to read and maintain the code being created.
 * 
 * @staticvar type $sf
 * @return \spitfire\SpitFire
 */
function spitfire() {
	static $sf = null;
	
	if ($sf !== null) { 
		return $sf; 
	} else {
		$sf = php_sapi_name() === 'cli'? new SpitFireCLI() : new SpitFire();
		$sf->prepare();
		return $sf;
	}
}

/**
 * 
 * Registers a new Application in Spitfire, allowing it to handle requests directed
 * to it.
 * 
 * @param string $name The name of the Application
 * @param string $namespace The namespace in which the requests will be sent to 
 *             the application.
 * @return App The App created by the system, use this to pass parameters and 
 *             configuration to the application.
 */
function app($name, $namespace) {
	$appName = $name . 'App';
	$app = new $appName(APP_DIRECTORY . $name . DIRECTORY_SEPARATOR, $namespace);
	spitfire()->registerApp($app, $namespace);
	return $app;
}

/**
 * Shorthand function to create / retrieve the model the application is using
 * to store data. We could consider this a little DB handler factory.
 *
 * @param Settings $options
 * @return \spitfire\storage\database\DB
 */
function db(Settings$options = null) {
	static $db = null;
	
	#If we're requesting the standard driver and have it cached, we use this
	if ($options === null && $db !== null) { return $db; }
	
	#If no options were passed, we try to fetch them from the environment
	$settings = Settings::fromURL($options? : Environment::get('db'));
	
	#Instantiate the driver
	$driver = 'spitfire\storage\database\drivers\\' . $settings->getDriver() . '\Driver';
	$driver = new $driver($settings);
	
	#If no options were provided we will assume that this is the standard DB handler
	if ($options === null) { $db = $driver; }
	
	#Return the driver
	return $driver;
}


/**
 * Returns HTML escaped string and if desired it adds ellipsis. If the string is
 * numeric it will reduce unnecessary decimals.
 * 
 * @param String $str
 * @param int $maxlength
 * @return String
 */
function __($str, $maxlength = false) {
	if ($maxlength) { $str = Strings::ellipsis ($str, $maxlength); }
	
	if (defined('ENT_HTML5')) 
		{ $str = htmlspecialchars($str, ENT_HTML5, Environment::get('system_encoding')); }
	else
		{ $str = htmlspecialchars($str, ENT_COMPAT, Environment::get('system_encoding')); }
	
	return $str;
}

/**
 * Translation helper.
 * 
 * Depending on the arguments this function receives, it will have one of several
 * behaviors.
 * 
 * If the first argument is a spitfire\locale\Locale and the function receives a
 * optional second parameter, then it will assign the locale to either the global
 * domain / the domain provided in the second parameter.
 * 
 * Otherwise, if the first parameter is a string, it will call the default locale's
 * say method. Which will translate the string using the standard locale.
 * 
 * If no parameters are provided, this function returns a DomainGroup object,
 * which provides access to the currency and date functions as well as the other
 * domains that the system has for translations.
 * 
 * @return string|DomainGroup 
 */
function _t() {
	static $domains = null;
	
	#If there are no domains we need to set them up first
	if ($domains === null) { $domains = new DomainGroup(); }
	
	#Get the functions arguments afterwards
	$args = func_get_args();
	
	#If the first parameter is a Locale, then we proceed to registering it so it'll
	#provide translations for the programs
	if (isset($args[0]) && $args[0] instanceof Locale) {
		$locale = array_shift($args);
		$domain = array_shift($args);
		
		return $domains->putDomain($domain, new Domain($domain, $locale));
	}
	
	#If the args is empty, then we give return the domains that allow for printing
	#and localizing of the data.
	if (empty($args)) {
		return $domains;
	}
	
	return call_user_func_array(Array($domains->getDefault(), 'say'), $args);
}

function current_context(ContextInterface$set = null) {
	static $context = null;
	if ($set!==null) {$context = $set;}
	return $context;
}

function console() {
	static $console = null;
	
	if ($console === null) {
		$console = new Console();
	}
	
	return $console;
}

function validate($target = null) {
	$targets  = array_filter(is_array($target)? $target : func_get_args());
	
	if (!empty($targets) && reset($targets) instanceof ValidatorInterface) {
		$messages = Array();
		
		#Retrieve the messages from the validators
		foreach ($targets as $target) {
			$messages = array_merge($messages, $target->getMessages());
		}
		
		if (!empty($messages)) { throw new ValidationException('Validation failed', 1604200115, $messages); }
		
		return $targets;
		
	} else {
		$validator = new Validator();
		$validator->setValue($target);
		return $validator;
	}
}

/**
 * Retrieves the current path from the request. This will retrieve the path 
 * without query string or document root.
 * 
 * @see http://www.spitfirephp.com/wiki/index.php/NgiNX_Configuration For NGiNX setup
 * @return string
 */
function getPathInfo() {
	$base_url = spitfire()->baseUrl();
	list($path) = explode('?', substr($_SERVER['REQUEST_URI'], strlen($base_url)));
	
	if (strlen($path) !== 0) { return $path; }
	else                     { return  '/';  }
}

function _def(&$a, $b) {
	return ($a)? $a : $b;
}

/**
 * This function is a shorthand for "Collection::fromArray" which also allows fluent
 * usage of the collection in certain environments where the PHP version still
 * limits that behavior.
 * 
 * @param mixed $elements
 * @return Collection
 */
function collect($elements = []) {
	return Collection::fromArray($elements);
}


/**
 * Creates a new URL. Use this class to generate dynamic URLs or to pass
 * URLs as parameters. For consistency (double base prefixes and this
 * kind of misshaps aren't funny) use this object to pass or receive URLs
 * as paramaters.
 * 
 * Please note that when passing a URL that contains the URL as a string like
 * "/hello/world?a=b&c=d" you cannot pass any other parameters. It implies that
 * you already have a full URL.
 * 
 * You can pass any amount of parameters to this class,
 * the constructor will try to automatically parse the URL as good as possible.
 * <ul>
 *		<li>Arrays are used as _GET</li>
 * 	<li>App objects are used to identify the namespace</li>
 *		<li>Strings that contain / or ? will be parsed and added to GET and path</li>
 *		<li>The rest of strings will be pushed to the path.</li>
 * </ul>
 */
function url() {
	#Get the parameters the first time
	$sf     = spitfire();
	$params = func_get_args();

	#Extract the app
	if (reset($params) instanceof App || $sf->appExists(reset($params))) {
		$app = $sf->getApp(array_shift($params));
	}
	else {
		$app = $sf;
	}

	#Get the controller, and the action
	$controller = null;
	$action     = null;
	$object     = Array();

	#Get the object
	while(!empty($params) && (!is_array(reset($params)) || (!$controller && $app->hasController(reset($params))))) {
		if     (!$controller) { $controller = array_shift($params); }
		elseif (!$action)     { $action     = array_shift($params); }
		else                  { $object[]   = array_shift($params); }
	}
	
	#Get potential environment variables that can be used for additional information
	#like loccalization
	$get          = array_shift($params);
	$environment  = array_shift($params);
	
	return new URL($app, $controller, $action, $object, 'php', $get, $environment);
}

/**
 * The within function is a math function that allows to determine whether a 
 * value is within a range and returns either the value, or the closest range
 * delimiter.
 * 
 * The first and the last parameter delimit the range. The second parameter is 
 * the one being tested.
 * 
 * <code>within(1,  50, 100); //Outputs:  50</code>
 * <code>within(1, 500, 100); //Outputs: 100</code>
 * <code>within(1, -50, 100); //Outputs:   1</code>
 * 
 * @param number $min
 * @param number $val
 * @param number $max
 * @return number
 */
function within($min, $val, $max) {
	return min(max($min, $val), $max);
}

function media() {
	static $dispatcher = null;
	
	if (!$dispatcher) {
		$dispatcher = new \spitfire\io\media\MediaDispatcher();
		$dispatcher->register('image/png', new \spitfire\io\media\GDManipulator());
		$dispatcher->register('image/jpg', new \spitfire\io\media\GDManipulator());
		$dispatcher->register('image/psd', new \spitfire\io\media\ImagickManipulator());
		$dispatcher->register('image/gif', new \spitfire\io\media\FFMPEGManipulator());
		$dispatcher->register('video/mp4', new \spitfire\io\media\FFMPEGManipulator());
		$dispatcher->register('video/quicktime', new \spitfire\io\media\FFMPEGManipulator());
		$dispatcher->register('image/jpeg', new \spitfire\io\media\GDManipulator());
		$dispatcher->register('image/vnd.adobe.photoshop', new \spitfire\io\media\ImagickManipulator());
	}
	
	return $dispatcher;
}

/**
 * 
 * @staticvar type $dispatcher
 * @param type $uri
 * @return \spitfire\storage\objectStorage\DriveDispatcher|spitfire\storage\objectStorage\NodeInterface
 */
function storage($uri = null) {
	
	static $dispatcher = null;
	
	if (!$dispatcher) {
		$dispatcher = new \spitfire\storage\objectStorage\DriveDispatcher();
		$dispatcher->register(new \spitfire\storage\drive\MountPoint('file://', '/'));
		$dispatcher->register(new \spitfire\storage\drive\MountPoint('app://', spitfire()->getCWD()));
		$dispatcher->register(new \spitfire\storage\drive\MountPoint('temp://', sys_get_temp_dir()));
	}
	
	if ($uri) {
		return $dispatcher->get($uri);
	}
	else {
		return $dispatcher;
	}
}

function request($url) {
	return new \spitfire\io\curl\Request($url);
}

function mime($file) {
	if (function_exists('mime_content_type')) { return mime_content_type($file); }
	else { return explode(';', system(sprintf('file -bi %s', escapeshellarg(realpath($file)))))[0]; }
}
