<?php namespace spitfire\exceptions;

use BadMethodCallException;
use Exception;
use spitfire\core\Environment;
use spitfire\core\Response;
use spitfire\core\Request;
use spitfire\io\template\Template;
use Throwable;
use function spitfire;

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

class ExceptionHandler {

	private $msgs     = Array();

	public function __construct() {
		set_exception_handler( Array($this, 'exceptionHandle'));
		register_shutdown_function( Array($this, 'shutdownHook'));
	}
	
	/**
	 * 
	 * @param \Throwable|\Exception $e
	 */
	public function exceptionHandle ($e) {
		if (!$e instanceof Exception && !$e instanceof Throwable) {
			throw new BadMethodCallException('Requires throwable type to work.', 1608011002);
		}
		
		if (!$e instanceof PublicException) {
			trigger_error(sprintf('Exception caught: %s (%s:%s). Trace: %s', $e->getMessage(), $e->getFile(), $e->getCode(), $e->getTraceAsString()), E_USER_WARNING);
		}
		
		try {
			while(ob_get_clean()); //The content generated till now is not valid. DESTROY. DESTROY!

			$response  = new Response(null);
			$basedir   = spitfire()->getCWD();
			$extension = Request::get()->getPath()->getFormat()? '.' . Request::get()->getPath()->getFormat() : '';
			
			$template = new Template([
				 "{$basedir}/bin/error_pages/{$e->getCode()}{$extension}.php",
				 "{$basedir}/bin/error_pages/default{$extension}.php",
				 "{$basedir}/bin/error_pages/{$e->getCode()}.php",
				 "{$basedir}/bin/error_pages/default.php"
			]);
			
			if ( $e instanceof PublicException) {
				$response->getHeaders()->status($e->getCode());
			}
			
			$response->setBody($template->render(!Environment::get('debug_mode')? [
				'code'    => $e instanceof PublicException? $e->getCode() : 500,
				'message' => $e instanceof PublicException? $e->getMessage() : 'Server error'
			] : [
				'code'      => $e instanceof PublicException? $e->getCode() : 500,
				'message'   => $e->getMessage(),
				'exception' => $e
			]));
			
			$response->send();

		} catch (Exception $e) { //Whatever happens, it won't leave this function
			echo '<!--'.$e->getMessage() . $e->getTraceAsString().'-->';
			ob_get_level() && ob_flush();
			die();
		}
	}
	
	public function shutdownHook () {
		$last_error = error_get_last();
		
		switch($last_error['type']?? 0){
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
			case E_PARSE:
			case E_RECOVERABLE_ERROR:
				while(ob_get_clean()); 
				
				$response  = new Response(null);
				$basedir   = spitfire()->getCWD();
				$extension = Request::get()->getPath()? '.' . Request::get()->getPath()->getFormat() : '';
					
				$template = new Template([
					 "{$basedir}/bin/error_pages/default{$extension}.php",
					 "{$basedir}/bin/error_pages/default.php"
				]);

				$response->setBody($template->render(!Environment::get('debug_mode')? [
					'code'    => 500,
					'message' => 'Server error'
				] : [
					'code'      => 500,
					'message'   => $last_error['message'] . "@$last_error[file] [$last_error[line]]",
				]));

				$response->send();
		}
		
	}

	public function log ($msg) {
		$this->msgs[] = $msg;
	}

	public function getMessages () {
		return $this->msgs;
	}
	
}
