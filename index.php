<?php

/**
 * This is the main file of Spitfire, it is in charge of loading
 * system settings (for custom operation) and also of summoning
 * spitfire and loading the adequate controller for every single
 * request. It also makes sure that error logging is sent to
 * terminal / log file instead of to the user.
 * 
 * @package Spitfire
 * @author César de la Cal <cesar@magic3w.com>
 * @copyright 2012 Magic3W - All rights reserved
 */

/* Define bootstrap settings. Environments are a better way to handle
 * config but we need to create them first.
 */
define ('BASEDIR', rtrim(dirname(__FILE__),DIRECTORY_SEPARATOR));
define ('SPITFIRE_BASEDIR',      BASEDIR.'/spitfire');
define ('APP_DIRECTORY',         'bin/apps/');
define ('CONFIG_DIRECTORY',      'bin/settings/');
define ('CONTROLLERS_DIRECTORY', 'bin/controllers/');
define ('ASSET_DIRECTORY',       'assets/');
define ('TEMPLATES_DIRECTORY',   'bin/templates/');
define ('SESSION_SAVE_PATH',     'bin/usr/sessions/');

/* Set error handling directives. AS we do not want Apache / PHP
 * to send the data to the user but to our terminal we will tell
 * it to output the errors. Thanks to this linux command:
 * # tail -f *logfile*
 * We can watch errors happening live. Grepping them can also help
 * filtering.
 */
ini_set("log_errors" , "0");
ini_set("error_log" , "logs/error_log.log");
ini_set("display_errors" , "0");

/* Include Spitfire core.
 */
include 'spitfire/bootstrap.php';

ini_set('memory_limit', '128M');/**/

/* Call the selected controller with the selected method. */
spitfire()->fire();
