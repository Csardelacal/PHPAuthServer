<?php namespace spitfire\validation\rules;

use spitfire\validation\ValidationError;


class TypeNumberValidationRule extends BaseRule
{
	
	/**
	 * Tests a value with this validation rule. Returns the errors detected for
	 * this element or boolean false on no errors.
	 * 
	 * @param mixed $value
	 * @return ValidationError|boolean
	 */
	public function test($value) {
		if ($value === null) {
			return false;
		}
		
		if (!is_numeric($value)) {
			return new ValidationError($this->getMessage(), $this->getExtendedMessage());
		}
		return false;
	}

}