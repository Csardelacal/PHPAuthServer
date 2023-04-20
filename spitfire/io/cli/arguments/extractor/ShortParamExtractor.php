<?php namespace spitfire\io\cli\arguments\extractor;

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

class ShortParamExtractor implements ExtractorInterface
{
	
	public function extract($argument) {
		if (\Strings::startsWith($argument, '-' )) {
			$pieces = explode('=', $argument, 2);
			$name   = str_split(substr(array_shift($pieces), 1));
			$value  = array_shift($pieces);
			
			if ($value) {
				$first = array_pop($name);
				$parameters[$first] = $value;
			}

			foreach ($name as $flag) { 
				$parameters[$flag] = isset($parameters[$flag])? $parameters[$flag] + 1 : 1; 
			}
			
			return $parameters;
		}
		
		return false;
	}

}