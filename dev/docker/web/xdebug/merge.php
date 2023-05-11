<?php

use commishes\qa\runner\coverage\Utils;

include __DIR__ . '/classes/Utils.php';

if (php_sapi_name() !== 'cli') {
	return;
}

if ($_ENV['XDEBUG_MODE'] !== 'off') {
	die('XDEBUG DETECTED');
}

ini_set('memory_limit', '8G');

/**
 * Get a location to merge all the coverage in.
 */
$basedir = '/var/www/coverage/' . $argv[1];

$runs = new FilesystemIterator( $basedir . DIRECTORY_SEPARATOR, FilesystemIterator::SKIP_DOTS );
$lcov = [];


/**
 * Merge each individual run into a single coverage array that can be fed to
 * PHPCC. This will then be processed into the reports we know and love.
 */
foreach ($runs as $run) {
	$data = json_decode(file_get_contents($run->getPathname()), true);
	$lcov = Utils::merge($lcov, $data);
}

echo json_encode($lcov);
