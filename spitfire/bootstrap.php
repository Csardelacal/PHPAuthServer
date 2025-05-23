<?php

use spitfire\autoload\NamespacedClassLocator;
use spitfire\autoload\RegisteredClassLocator;

/*
 * This is the bootstrap file of spitfire. It imports all the basic files that 
 * are required for Spitfire to run.
 * 
 * It also creates the Autoload and ExceptionHandler instances that Spitfire will
 * use to retrieve classes that can be used by the user. This happens in the 
 * following order:
 * 
 * * Include core files
 * * Create autoload and Exception handler
 * 
 * This file does deliberately not import the user settings nor does it start
 * Spitfire. It will just prepare the components and files that Spitfire will need
 * in case it is invoked.
 * 
 * Usually, when working on a website index.php will instantly call spitfire()->light()
 * which will cause Spitfire to capture the Request from the webserver, handle it
 * and answer accordingly.
 */

/*
 * In case the scripts are run in CLI we would like to know everything that goes
 * wrong, since this environments are usually used for testing or by devs.
 */
if (php_sapi_name() === 'cli') {
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}

/*
 * If the locations of the spitfire and base directory are not defined we define
 * them here. This should ensure that the aplication continues to work properly
 * during testing and under windows environments that do not have proper linking
 */
if (!defined('SPITFIRE_BASEDIR')) { define('SPITFIRE_BASEDIR', rtrim(dirname(__FILE__), '\/')); }
if (!defined('BASEDIR')         ) { define('BASEDIR', rtrim(dirname(dirname(__FILE__)), '\/')); }

#Start loading the core files.
require_once SPITFIRE_BASEDIR . '/Strings.php';
require_once SPITFIRE_BASEDIR . '/ClassInfo.php';  //TODO: Remove - Deprecated
require_once SPITFIRE_BASEDIR . '/autoload/Autoload.php';
require_once SPITFIRE_BASEDIR . '/autoload/ClassLocator.php';
require_once SPITFIRE_BASEDIR . '/autoload/NamespacedClassLocator.php';

#Create the autoload. Once started it will allow you to register classes and 
#locators to retrieve new classes that are missing to your class-space
$autoload = new spitfire\autoload\AutoLoad();

#These are basic locators that allow spitfire to find it's own classes. Which it's 
#gonna need to then make the user space classes work
$autoload->registerLocator(new NamespacedClassLocator('spitfire', SPITFIRE_BASEDIR));
$autoload->registerLocator(new RegisteredClassLocator(BASEDIR));
$autoload->registerLocator(new spitfire\autoload\AppClassLocator(BASEDIR));

#Register the loaders for the classes within user space
$autoload->registerLocator(new NamespacedClassLocator('', BASEDIR . '/bin/controllers', 'Controller'));
$autoload->registerLocator(new NamespacedClassLocator('', BASEDIR . '/bin/models',      'Model'));
$autoload->registerLocator(new NamespacedClassLocator('', BASEDIR . '/bin/locales',     'Locale'));
$autoload->registerLocator(new NamespacedClassLocator('', BASEDIR . '/bin/views',       'View'));
$autoload->registerLocator(new NamespacedClassLocator('', BASEDIR . '/bin/beans',       'Bean'));
$autoload->registerLocator(new NamespacedClassLocator('', BASEDIR . '/bin/directors',   'Director'));
$autoload->registerLocator(new NamespacedClassLocator('', BASEDIR . '/bin/classes'));

#Import the locations of the most critical components to Spitfire so it has no
#need to look for them.
#require_once SPITFIRE_BASEDIR . '/autoload_core_files.php';

#Create the exceptionhandler that will capture errors and try to present useful
#information to the user.
#new spitfire\exceptions\ExceptionHandler();
