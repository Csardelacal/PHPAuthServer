<?php namespace attribute\validator;

use attribute\AttributeValidatorInterface;

class StringMinLengthValidator implements AttributeValidatorInterface
{
	
	private $settings;
	
	public function __construct() {
		//Nothing to do on this constructor
	}

	public function getName() {
		return "Minimum string length";
	}

	public function load($settings) {
		$this->settings = $settings;
	}

	public function test($value) {
		return is_string($value) && strlen($value) > $this->settings? false : new \spitfire\validation\ValidationError($this->getErrorMsg(), $this->getDescription());
	}

	public function validates() {
		return "string";
	}

	public function getErrorMsg() {
		return "String too short";
	}

	public function getDescription() {
		return "Must be longer than {$this->settings} characters";
	}

}
