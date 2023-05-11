<?php


$test = $_GET['testname'];

putenv('XDEBUG_MODE=off');

$command = sprintf(
	'XDEBUG_MODE=off php %s/merge.php %s',
	__DIR__,
	$test
);

passthru($command);

