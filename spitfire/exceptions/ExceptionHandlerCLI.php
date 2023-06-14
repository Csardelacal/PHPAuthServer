<?php namespace spitfire\exceptions;

use BadMethodCallException;
use Exception;
use Throwable;
use function console;

/**
 * Silent exception handler.
 * 
 * Whenever an uncaught exception reaches the server it will use this
 * function for "discrete" failure. The function retrieves (depending
 * on the error) a error page and logs the error so it can be  
 * analyzed later.
 * In case there is a failover, and the function fails or cannot
 * find a file to display the error page it will try to handle the error
 * by causing a "white screen of death" to the user adding error information
 * to a HTML comment block. As it is the only failsafe way of communication
 * when there is a DB Error or permission error on the log files.
 * 
 * @param Exception $e
 */

class ExceptionHandlerCLI
{

	private $msgs     = Array();

	public function __construct() {
	}
	
	/**
	 * 
	 * @param \Throwable|\Exception $e
	 */
	public function exceptionHandle ($e) {
	}
	
	public function shutdownHook () {
	}

	public function log ($msg) {
		$this->msgs[] = $msg;
	}

	public function getMessages () {
		return $this->msgs;
	}
	
}
