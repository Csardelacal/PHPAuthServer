<?php

use spitfire\validation\ValidationRule;

class ClosureValidationRule implements ValidationRule
{
	
	private $c;
	
	public function __construct(Closure$c) {
		$this->c = $c;
	}
	
	public function test($value) {
		$c = $this->c;
		return $c($value);
	}

}

