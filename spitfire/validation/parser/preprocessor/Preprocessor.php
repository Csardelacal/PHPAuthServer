<?php namespace spitfire\validation\parser\preprocessor;

use spitfire\exceptions\PrivateException;

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

class Preprocessor
{
	
	private $modules;
	
	public function __construct() {
		$this->modules = [
			new Module('"', '"', true,  function($e) { return new \spitfire\validation\parser\Token($e); }), //Literals
			new Module('(', ')', false, function($e) { return new \spitfire\validation\parser\GroupComponent($e); }), //Parenthesis
			new Module('[', ']', false, function($e) { return new \spitfire\validation\parser\Options($e); })  //Brackets
		];
	}
	
	public function prepare($str) {
		
		$current = '';
		$result  = $initial = new Result(null, null);

		for($i = 0; $i < strlen($str); $i++) {
			
			if ($result->getModule() && $result->getModule()->isEscaped() && $str[$i - 1] === '\\') { 
				$current.= $str[$i]; 
				continue;
			}
			elseif ($result->getModule() && $result->getModule()->isEscaped() && $str[$i] === '\\') { 
				continue;
			}
			elseif ($result->getModule() && $str[$i] === $result->getModule()->getClose() && ($result->getModule()->getOpen() !== $result->getModule()->getClose() || $result->getParent())) {
				$result->append($current);
				$result->getParent()->append($result->end());
				$current = '';
				$result = $result->getParent();
				continue;
			}
			
			foreach($this->modules as $module) {
				
				if ($str[$i] === $module->getOpen()) {
					$result->append($current);
					$current = '';
					$result  = new Result($module, $result);
					/*
					 * I hate this structure, and I hope I don't have to use it ever 
					 * again. But here it just makes so much more sense than introducing
					 * an extra variable that it pains me.
					 */
					continue 2;
				}
				
			}
			
			$current.= $str[$i];
		}
		
		if ($result !== $initial) {
			throw new PrivateException('Malformed expression!', 1805211227);
		}
		
		return new \spitfire\validation\parser\GroupComponent($result->getElements());
	}
	
}
