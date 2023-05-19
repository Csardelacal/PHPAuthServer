<?php

ini_set('memory_limit', '4G');

$cov = json_encode(xdebug_get_code_coverage(), true);
$uniqid = uniqid("xdebug-", true);

$folder = file_exists('/tmp/current.txt')? file_get_contents('/tmp/current.txt') : '/var/www/coverage/incoming/';

if (php_sapi_name() === 'cli' || strpos($_SERVER['SERVER_NAME'], 'coverage') !== false) {
	return;
}

/**
 * Create a random file inside the /incoming folder. This will have the effect that the system
 * can collect code-coverage for several requests before generating a single unified coverage
 * file in the test-runner.
 * 
 * @see commishes\qa\runner\SeleniumTestCase::tearDown
 */
file_put_contents(
	$folder . $uniqid . '.cov', 
	$cov
);
