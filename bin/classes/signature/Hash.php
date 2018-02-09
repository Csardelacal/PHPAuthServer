<?php namespace signature;

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

/**
 * Signatures are a method to identify two servers communicating with each other.
 * A server can sign a set of data and the receiving server can (with knowledge
 * of the data being sent) verify that the origin server is the one it claims to
 * be.
 * 
 * An example would be a server identifying itself with a signature that contains
 * it's app ID, app Secret and a random salt to prevent the request from being
 * recycled.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 * @todo Technically this class should be named <code>Hash</code>
 */
class Hash
{
	
	/**
	 * This constant indicates the usage of SHA512 as hashing algorhythm. As of
	 * 2018 this algo is sufficient for the application.
	 * 
	 * @link https://en.wikipedia.org/wiki/SHA-2
	 */
	const ALGO_SHA512  = 'sha512';
	
	/**
	 * This constant points to the default algorhythm. This constant is updated 
	 * as the algo is changed.
	 */
	const ALGO_DEFAULT = self::ALGO_SHA512;
	
	/**
	 * Name of the algorhythm to be used to hash the signature.
	 *
	 * @var string
	 */
	private $algo;
	
	private $components;
	
	/**
	 * 
	 * @param type $algo
	 * @param type $_
	 */
	public function __construct($algo, $_) {
		$this->components = func_get_args();
		$this->algo       = array_shift($this->components);
	}
	
	public function getAlgo() {
		return $this->algo;
	}
	
	public function hash() {
		$components   = $this->components;
		
		/*
		 * Reconstruct the original signature with the data we have about the 
		 * source application to verify whether the apps are the same, and
		 * should therefore be granted access.
		 */
		switch(strtolower($this->algo)) {
			case 'sha512':
				$calculated = hash('sha512', implode('.', array_filter($components)));
				break;
			default:
				throw new \Exception('Invalid algorithm', 400);
		}
		
		return $calculated;
	}
	
	public function verifier() {
		return new Checksum($this->algo, $this->hash());
	}
	
}