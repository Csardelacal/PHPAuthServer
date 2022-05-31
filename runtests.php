<?php

use BrowserStack\Local;

if (php_sapi_name() !== 'cli') {
	exit;
}

require 'vendor/autoload.php';

$caps = [
	[
		"os" => "Windows",
		"os_version" => "11",
		"browser" => "chrome",
		"browser_version" => "101.0",
		"build" => "browserstack-build-1",
		"name" => "Parallel test 1",
		"browserstack.local" => "true"
	],
	[
		"os" => "Windows",
		"os_version" => "10",
		"browser" => "firefox",
		"browser_version" => "latest",
		"build" => "browserstack-build-1",
		"name" => "Parallel test 2",
		"browserstack.local" => "true"
	],
	[
		"browserName" => "android",
		"realMobile" => "true",
		"device" => "Samsung Galaxy S20",
		"os_version" => "10.0",
		"build" => "browserstack-build-1",
		"name" => "Parallel test 3",
		"browserstack.local" => "true"
	]
];

error_reporting(E_ALL);
$username = getenv("BROWSERSTACK_USERNAME");
$accessKey = getenv("BROWSERSTACK_ACCESS_KEY");

# Creates an instance of Local
$tunnel = new Local();

# You can also set an environment variable - "BROWSERSTACK_ACCESS_KEY".
$bs_local_args = array("key" => $accessKey);

# Starts the Local instance with the required arguments
$tunnel->start($bs_local_args);

foreach ($caps as $cap) {
	$env = $_ENV;
	$env['BROWSERSTACK_CAPS'] = json_encode($cap);
	
	$process = proc_open(
		'php ./vendor/bin/phpunit tests',
		array(
			0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
			1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
			2 => array("pipe", "w")
		),
		$pipes,
		__DIR__,
		$env
	);
	
	if (!is_resource($process)) {
		$tunnel->stop();
		die('Not a resource');
	}
	
	fclose($pipes[0]);
	
    echo stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    echo stream_get_contents($pipes[2]);
    fclose($pipes[2]);
}

$tunnel->stop();
