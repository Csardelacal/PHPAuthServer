<?php namespace spitfire\io\cli\arguments;

use spitfire\io\cli\arguments\extractor\LongParamExtractor;
use spitfire\io\cli\arguments\extractor\ShortParamExtractor;
use spitfire\io\cli\arguments\extractor\STDINExtractor;
use spitfire\io\cli\arguments\extractor\StopCommandExtractor;

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

class Parser
{
	
	private $extractors;
	
	public function __construct() {
		$this->extractors = [
			new StopCommandExtractor(),
			new LongParamExtractor(),
			new STDINExtractor(),
			new ShortParamExtractor()
		];
	}
	
	/**
	 * 
	 * @param string[] $argv
	 * @return CLIArguments
	 */
	public function read($argv) {
		
		$script     = array_shift($argv);
		$parameters = [];
		$arguments  = [];
		
		
		foreach ($argv as $arg) {
			foreach ($this->extractors as $extractor) {
				
				$r = $extractor->extract($arg);
				
				if ($r === false) {
					//Do nothing, the extractor can't handle this data
				}
				
				elseif (is_array($r)) {
					$parameters = array_merge($parameters, $r);
					continue 2;
				}
				
				elseif (is_string($r)) {
					$arguments[] = $r;
					continue 2;
				}
			}
				
			$arguments[] = $arg;
			
		}
		
		return new CLIArguments($script, $arguments, $parameters);
	}
	
}
