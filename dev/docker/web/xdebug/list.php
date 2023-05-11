<?php

$basedir = '/var/www/coverage/';
$runs = new FilesystemIterator( $basedir . DIRECTORY_SEPARATOR, FilesystemIterator::SKIP_DOTS );
$return = [];

/**
 * Merge each individual run into a single coverage array that can be fed to
 * PHPCC. This will then be processed into the reports we know and love.
 */
foreach ($runs as /** @var SplFileInfo */$run) {
	/**
	 * 
	 */
	if (!is_dir($run->getPathname())) {
		continue;
	}
	
	$return[] = $run->getFilename();
}

echo json_encode($return);
