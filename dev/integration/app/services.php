<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use spitfire\provider\Container;

/**
 *
 * @todo Wrap this into the kernel
 */
function container() : Container
{
	static $container = null;
	
	if ($container === null) {
		$container = new Container();
	}
	
	return $container;
}

{
	$logger = new Logger('log');
	$logger->pushHandler(new StreamHandler(fopen('php://stderr', 'w')));
	container()->set(LoggerInterface::class, $logger);
}
