<?php namespace spitfire\validation\rules;

use spitfire\validation\ValidationRule;
use spitfire\validation\Validator;

/* 
 * The MIT License
 *
 * Copyright 2018 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

trait AcceptsRules 
{
	
	
	/**
	 * Allows the application using this to apply additional restrictions to the 
	 * base ones (optional) to restrict the possibilities of the data being valid
	 * further.
	 */
	abstract public function addRule(ValidationRule$rule);
	
	/**
	 * Marks this as required, therefore, forcing the content of this validator
	 * to be non-empty
	 * 
	 * @param string $message
	 * @param string $extended
	 * @return $this
	 */
	public function required($message, $extended) {
		$this->addRule(new EmptyValidationRule($message, $extended));
		return $this;
	}
	
	/**
	 * Adds minimum length validation to this object. It will cause an error if
	 * the data tested is not long enough, this is meant for strings, and will 
	 * probably cause faulty behavior when testing agains other stuff.
	 * 
	 * @param int $length
	 * @param string $msg
	 * @param string $longmsg
	 * @return Validator
	 */
	public function minLength($length, $msg, $longmsg = '') {
		$this->addRule(new MinLengthValidationRule($length, $msg, $longmsg));
		return $this;
	}
	
	
	/**
	 * Adds maximum length validation to this object. It will cause an error if
	 * the data tested is longer than expected, this is meant for strings, and will 
	 * probably cause faulty behavior when testing agains other stuff.
	 * 
	 * @param int $length
	 * @param string $msg
	 * @param string $longmsg
	 * @return Validator
	 */
	public function maxLength($length, $msg, $longmsg = '') {
		$this->addRule(new MaxLengthValidationRule($length, $msg, $longmsg));
		return $this;
	}
	
	/**
	 * Validates a content as email. This evaluates the string value of what it 
	 * receives and may cause unexpected behavior.
	 * 
	 * @param string $msg
	 * @param string $longmsg
	 * @return Validator
	 */
	public function asEmail($msg, $longmsg = '') {
		$this->addRule(new FilterValidationRule(FILTER_VALIDATE_EMAIL, $msg, $longmsg));
		return $this;
	}
	
	/**
	 * Validates a content as a URL. This evaluates the string value of what it 
	 * receives and may cause unexpected behavior.
	 * 
	 * @param string $msg
	 * @param string $longmsg
	 * @return Validator
	 */
	public function asURL($msg, $longmsg = '') {
		$this->addRule(new FilterValidationRule(FILTER_VALIDATE_URL, $msg, $longmsg));
		return $this;
	}
	
	/**
	 * 
	 * @param int $filter An ID from the FILTER_* constants
	 * @param string $message
	 * @param string $extended
	 * @return Validator
	 */
	public function filter($filter, $message, $extended = '') {
		$this->addRule(new FilterValidationRule($filter, $message, $extended));
		return $this;
	}
	
	/**
	 * Matches the content against a regular expression. If the content matches 
	 * the regular expression validation will be considered successful.
	 * 
	 * @param string $regex
	 * @param string $message
	 * @param string $extended
	 * @return $this
	 */
	public function match($regex, $message, $extended = '') {
		$this->addRule(new RegexValidationRule($regex, $message, $extended));
		return $this;
	}
	
	/**
	 * Checks whether the content of this validator contains a positive integer.
	 * 
	 * @param string $message
	 * @param string $extended
	 * @return $this
	 */
	public function positive($message, $extended) {
		$this->addRule(new PositiveNumberValidationRule($message, $extended));
		return $this;
	}
	
	/**
	 * Creates a validation group. This allows to create more complicated rules
	 * that merge the results of several smaller rules.
	 * 
	 * @return ValidationRuleGroup
	 */
	public function group() {
		$this->addRule($v = new ValidationRuleGroup($this));
		return $v;
	}
	
}
