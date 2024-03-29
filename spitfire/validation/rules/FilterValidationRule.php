<?php namespace spitfire\validation\rules;

use spitfire\validation\ValidationError;
use spitfire\validation\ValidationRule;

/**
 * A filter based validation rule allows you to use premade PHP filters to validate
 * your content. Please note that a filter that sanitizes may cause unwanted
 * behavior or unexpected ones.
 * 
 * @author César de la Cal <cesar@magic3w.com>
 * @last-revision 2013-11-12
 */
class FilterValidationRule implements ValidationRule
{
	/**
	 * The filter being applied. This is one of the <code>FILTER_VALIDATION_*</code> constants
	 * defined in PHP's core, you can use any of those to place here.
	 * @var int
	 */
	private $filter;
	
	/**
	 * A message the validation error generated by this object should carry to give
	 * the end user information about the reason his input was rejected.
	 * @var string
	 */
	private $message;
	
	/**
	 * Additional information given to the user in case the validation did not 
	 * succeed. This message can hold additional infos on how to solve the error.
	 * @var string
	 */
	private $extendedMessage;
	
	/**
	 * Creates a new validation rule that relies on PHP's filters. You need to pass
	 * one of the VALIDATION_FILTER_* constants to it so it works. Many of this
	 * functions provide useful tools for quick validation of common tasks.
	 * 
	 * @param int $filter One of the VALIDATION_FILTER_* constants
	 * @param string $message The message returned when an error is found
	 * @param string $extendedMessage Additional error information
	 */
	public function __construct($filter, $message, $extendedMessage = '') {
		$this->filter = $filter;
		$this->message = $message;
		$this->extendedMessage = $extendedMessage;
	}
	
	/**
	 * Tests a value with this validation rule. Returns the errors detected for
	 * this element or boolean false on no errors.
	 * 
	 * @param mixed $value The value tested.
	 * @return ValidationError|boolean A validation error or boolean on success
	 */
	public function test($value) {
		if ($value === null) {
			return false;
		}
		
		if (!filter_var($value, $this->filter)) {
			return new ValidationError($this->message, $this->extendedMessage);
		}
		
		return false;
	}

}