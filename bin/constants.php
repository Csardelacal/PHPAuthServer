<?php

/* Define bootstrap settings. Environments are a better way to handle
 * config but we need to create them first.
 */
define('BASEDIR', rtrim(dirname(dirname(__FILE__)), DIRECTORY_SEPARATOR));
define('SPITFIRE_BASEDIR', BASEDIR.'/spitfire');
define('APP_DIRECTORY', 'bin/apps/');
define('CONFIG_DIRECTORY', 'bin/settings/');
define('CONTROLLERS_DIRECTORY', 'bin/controllers/');
define('ASSET_DIRECTORY', 'assets/');
define('TEMPLATES_DIRECTORY', 'bin/templates/');
define('SESSION_SAVE_PATH', 'bin/usr/sessions/');
