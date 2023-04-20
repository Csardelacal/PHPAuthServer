<?php namespace spitfire\validation\parser;

use Closure;
use spitfire\exceptions\PrivateException;
use spitfire\validation\parser\preprocessor\Preprocessor;
use spitfire\validation\rules\EmptyValidationRule;
use spitfire\validation\rules\FilterValidationRule;
use spitfire\validation\rules\InValidationRule;
use spitfire\validation\rules\LengthValidationRule;
use spitfire\validation\rules\NotValidationRule;
use spitfire\validation\rules\PositiveNumberValidationRule;
use spitfire\validation\rules\TypeNumberValidationRule;
use spitfire\validation\rules\TypeStringValidationRule;
use spitfire\validation\ValidationRule;
use spitfire\validation\ValidatorGroup;
use spitfire\validation\ValidatorInterface;
use function __;

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

/**
 * This parser allows a developer to configure the application to validate the 
 * incoming data using expressions inside the docblocks (or any similar mechanism)
 * 
 * @todo Consider a more elaborate mechanism for rule creation. Generally speaking,
 * the creation of rules should be rather simple. Since this element only interfaces
 * with the actual rules. But maybe it could be interesting.
 */
class Parser
{
	
	/**
	 * The preprocessor basically is in charge of splicing the expression by delimited
	 * blocks (like parenthesis or brackets) and group the items that are on the 
	 * same level.
	 *
	 * @var Preprocessor
	 */
	private $preprocessor;
	
	/**
	 * The logic processors break the expression apart whenever a logic operator
	 * is found (like AND or OR) and create validators that allow the application
	 * to verify the data received.
	 *
	 * @var LogicProcessor[]
	 */
	private $logic = [];
	
	/**
	 * Contains a list of callable items that allow your application to instance 
	 * ValidationRules from the expressions provided.
	 *
	 * @var Closure[]
	 */
	private $rules = [];
	
	/**
	 * Creates a new parser for validation expressions. These expressions allow to
	 * quickly provide the application with validation for input.
	 */
	public function __construct() {
		
		$this->preprocessor = new Preprocessor();
		$this->logic[] = new LogicProcessor(ValidatorGroup::TYPE_OR);
		$this->logic[] = new LogicProcessor(ValidatorGroup::TYPE_AND);
		
		#Create the default rules
		$this->rules['string'] = function() { return new TypeStringValidationRule('Accepts only strings'); };
		$this->rules['email']  = function() { return new FilterValidationRule(FILTER_VALIDATE_EMAIL, 'Invalid email provided'); };
		$this->rules['url']    = function() { return new FilterValidationRule(FILTER_VALIDATE_URL, 'Invalid email provided'); };
		$this->rules['positive']=function() { return new PositiveNumberValidationRule('Value must be a positive number'); };
		$this->rules['number'] = function() { return new TypeNumberValidationRule('Value must be a number'); };
		$this->rules['required']=function() { return new EmptyValidationRule('Value is required. Cannot be empty'); };
		$this->rules['not']    = function($value) { return new NotValidationRule($value, sprintf('Value "%s" is not allowed', $value)); };
		$this->rules['in']     = function() { return new InValidationRule(func_get_args(), sprintf('Value must be one of (%s)', __(implode(', ', func_get_args())))); };
		$this->rules['length'] = function($min, $max = null) { return new LengthValidationRule($min, $max, sprintf('Field length must be between %s and %s characters', $min, $max)); };
	}
	
	/**
	 * Parses the expression, extracting the Validator used to ensure that the 
	 * data provided by the app's user is correct.
	 * 
	 * @param string $string
	 * @return ValidatorInterface
	 */
	public function parse($string) : ValidatorInterface {
		$result = $this->preprocessor->prepare($string)->tokenize();
		
		foreach ($this->logic as $l) {
			$l->run($result);
		}
		
		return $result->make($this);
	}
	
	/**
	 * Sets a rule. If the rule already existed, it will be overwritten.
	 * 
	 * @param string $name
	 * @param \Closure $callable
	 */
	public function rule($name, $callable) {
		$this->rules[$name] = $callable;
	}
	
	/**
	 * Makes a set of rules from the data parsed by the preprocessor. Please note
	 * that the system will assign any options parsed to the previous rule.
	 * 
	 * @param Options $from
	 * @return ValidationRule[]
	 * @throws PrivateException
	 */
	public function makeRules($from) {
		#Never forget to initialize variables! :D
		$_ret = [];
		
		/*
		 * Loop over the data parsed.
		 */
		for($i = 0; $i < count($from); $i++) {
			
			/*
			 * Get the name of the rule to be used. This will later be used to test
			 * whether the system has the rule available.
			 */
			$rule = $from[$i]->getContent();
			
			/*
			 * If the item next to the current one is a Options instance, we will
			 * combine the two options AND skip the next item. Otherwise, we just 
			 * set options to empty.
			 * 
			 * I personally dislike how this works, it's a bit hacky to skip the 
			 * next item by incrementing the counter, but it does work...
			 */
			if (isset($from[$i + 1]) && $from[$i+1] instanceof Options) {
				$options = $from[$i+1];
				$i++;
			}
			else {
				$options = null;
			}
			
			$_ret[] = $this->getRule($rule, $options);
		}

		return array_filter($_ret);
	}
	
	public function getRule($rule, $options) {
		
		/*
		 * Check if the rule being used does indeed exist. Otherwise the program
		 * cannot continue.
		 * 
		 * While this may be a nuissance, it ensures that the system does not 
		 * skip any rule the user defined. I'd rather have it fail if the validator
		 * was not found than skipping the rule and letting data through unvalidated.
		 */
		if (!isset($this->rules[$rule])) { throw new PrivateException('Invalid rule: ' . $rule, 1805171527); }
		
		/*
		 * Create the rule and add it to the stack of rules ot be executed.
		 */
		return call_user_func_array($this->rules[$rule], $options? $options->getItems() : []);
	}
}
