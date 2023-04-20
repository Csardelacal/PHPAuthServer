<?php namespace spitfire\validation\rules;

use spitfire\validation\ValidationError;

/**
 * Validates that the length of a content is smaller than the indicated minimum
 * length. This validates only data as strings which may cause unexpected 
 * behavior if you try to test the length of an array.
 * 
 * @author César de la Cal <cesar@magic3w.com>
 */
class NotValidationRule extends BaseRule
{
	/**
	 *
	 * @var mixed 
	 */
	private $value;
	
	/**
	 * Creates a maximum length validation rule. This will provide a way to test
	 * whether a string is longer than allowed before using or storing it.
	 * 
	 * @param mixed  $value
	 * @param string $message
	 * @param string $extendedMessage
	 */
	public function __construct($value, $message, $extendedMessage = '') {
		$this->value = $value;
		
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
		if ($value === $this->value) {
			return new ValidationError($this->getMessage(), $this->getExtendedMessage());
		}
		
		return false;
	}

}