<?php namespace mail\spam\domain;

use spitfire\validation\ValidationError;
use spitfire\validation\ValidationRule;

/* 
 * The MIT License
 *
 * Copyright 2017 César de la Cal Bretschneider <cesar@magic3w.com>.
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

class SpamDomainValidationRule implements ValidationRule
{
	
	/**
	 *
	 * @var SpamDomainTester 
	 */
	private $tester;
	
	public function __construct(StorageInterface $reader) {
		$this->tester = new SpamDomainTester($reader);
	}
	
	/**
	 * 
	 * {@inheritdoc}
	 */
	public function test($value) {
		$domain = new Domain($value);
		
		if ($this->tester->check($domain)) {
			
			/*
			 * If the domain is found in our spam report system we will return a 
			 * new Validation error for the domain. The application can then 
			 * appropriately pretty print the errors for the input.
			 */
			return new ValidationError(
				'Domain rejected', 
				'This domain was reported as a source for email spam and is blocked'
			);
		}
		
		return true;
	}

}