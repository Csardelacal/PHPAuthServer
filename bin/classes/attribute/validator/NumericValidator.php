<?php namespace attribute\validator;

use attribute\AttributeValidatorInterface;
use spitfire\validation\ValidationError;

class NumericValidator implements AttributeValidatorInterface
{
	
	public function __construct() {
		
	}

	public function getDescription() {
		return 'Value must contain only numeric data';
	}

	public function getErrorMsg() {
		return 'Numbers only';
	}

	public function getName() {
		return 'Numbers only';
	}

	public function load($settings) {
		return;
	}

	public function test($value) {
		return is_numeric($value)? false : new ValidationError($this->getErrorMsg(), $this->getDescription());
	}

	public function validates() {
		return 'string';
	}

}