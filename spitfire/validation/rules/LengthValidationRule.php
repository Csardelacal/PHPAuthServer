<?php namespace spitfire\validation\rules;

use spitfire\validation\ValidationError;

/**
 * Validates that the length of a content is smaller than the indicated minimum
 * length. This validates only data as strings which may cause unexpected 
 * behavior if you try to test the length of an array.
 * 
 * @author César de la Cal <cesar@magic3w.com>
 */
class LengthValidationRule extends BaseRule
{
	/**
	 * The minimum length a string passed to this element must have for the test
	 * to be passed successfully.
	 * 
	 * @var int
	 */
	private $min;
	
	/**
	 * The maximum length a string passed to this element can have for the test
	 * to be passed successfully.
	 * 
	 * @var int
	 */
	private $max;
	
	/**
	 * Creates a maximum length validation rule. This will provide a way to test
	 * whether a string is longer than allowed before using or storing it.
	 * 
	 * @param int $min
	 * @param int $max
	 * @param string $message
	 * @param string $extendedMessage
	 */
	public function __construct($min, $max, $message, $extendedMessage = '') {
		$this->max = $max;
		$this->min = $min;
		
		parent::__construct($message, $extendedMessage);
	}
	
	
	/**
	 * Tests a value with this validation rule. Returns the errors detected for
	 * this element or boolean false on no errors.
	 * 
	 * @param mixed $value
	 * @return ValidationError|boolean
	 */
	public function test($value) {
		if (function_exists('mb_strlen')) {
			return ($this->max && mb_strlen($value) > $this->max) || mb_strlen($value) < $this->min? 
				new ValidationError($this->getMessage(), $this->getExtendedMessage()) : 
				false;
		}
		elseif (($this->max && strlen($value) > $this->max) || strlen($value) < $this->min) {
			return new ValidationError($this->getMessage(), $this->getExtendedMessage());
		}
		
		return false;
	}

}