<?php namespace spitfire\core\router\reverser;

use spitfire\core\Path;

class ClosureReverser implements RouteReverserInterface
{
	
	private $closure;
	
	public function __construct($c) {
		$this->closure = $c;
	}

	/** 
	 * @inheritdoc 
	 */
	public function reverse(Path$path, $explicit = false) {
		$c = $this->closure;
		return $c($path, $explicit);
	}

}
