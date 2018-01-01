<?php

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

class Signature
{
	
	private $algo;
	
	private $components;
	
	private $salt;
	
	/**
	 * 
	 * @param type $algo
	 * @param type $_
	 */
	public function __construct($algo, $_) {
		$this->components = func_get_args();
		$this->algo       = array_shift($this->components);
	}
	
	public function salt($salt = null) {
		$this->salt = $salt;
		return $this;
	}
	
	public function hash() {
		$components   = $this->components;
		$components[] = $this->salt;
		
		/*
		 * Reconstruct the original signature with the data we have about the 
		 * source application to verify whether the apps are the same, and
		 * should therefore be granted access.
		 */
		switch(strtolower($this->algo)) {
			case 'sha512':
				$calculated = hash('sha512', implode('.', array_filter($components, function ($e) { return $e !== null; })));
				break;
			default:
				throw new \Exception('Invalid algorithm', 400);
		}
		
		return $calculated;
	}
	
	public function verify($hash) {
		return $this->hash() === $hash;
	}
	
	/**
	 * Splits up a 
	 * 
	 * @param string $from
	 * @return string[]
	 * @throws PublicException
	 */
	public static function extract($from) {
		$signature = explode(':', $from);
		
		switch(count($signature)) {
			case 4:
				list($algo, $src, $salt, $hash) = $signature;
				$target = null;
				break;
			case 5:
				list($algo, $src, $target, $salt, $hash) = $signature;
				break;
			case 6:
				list($algo, $src, $target, $contextstr, $salt, $hash) = $signature;
				$context = explode(',', $contextstr);
				break;
			default:
				throw new PublicException('Invalid signature', 400);
		}
		
		return [$algo, $src, $target, $context, $salt, $hash];
	}
	
}