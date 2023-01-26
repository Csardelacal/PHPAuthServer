<?php namespace magic3w\phpauth\sdk\signature;

use Exception;

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
 * @todo Remove
 * @deprecated
 */
class Hash
{
	
	/**
	 * This constant indicates the usage of SHA512 as hashing algorithm. As of
	 * 2018 this algo is sufficient for the application.
	 *
	 * @link https://en.wikipedia.org/wiki/SHA-2
	 */
	const ALGO_SHA512  = 'sha512';
	
	/**
	 * This constant points to the default algorithm. This constant is updated
	 * as the algo is changed.
	 */
	const ALGO_DEFAULT = self::ALGO_SHA512;
	
	/**
	 * The separator used to separate the components before running the hashing
	 * function. This should make the debugging simpler and prevent collisions
	 * when using short data.
	 *
	 * For example, when hashing (1, 11) and (11, 1) you could have a collision
	 * if no separator is provided since both options would hash(111), this would
	 * make it rather easy to vector an attack against the system.
	 */
	const SEPARATOR = '.';
	
	/**
	 * Name of the algorithm to be used to hash the signature.
	 *
	 * @var string
	 */
	private $algo;
	
	/**
	 * PHPAS usually hashes several pieces of data as part of a signature. Instead
	 * of providing this object with a pre-concatenated string, we use an array -
	 * which is concatenated before being hashed.
	 *
	 * @var string[]
	 */
	private $components;
	
	/**
	 * Creates a new hash. The first parameter is the algorithm to be used to
	 * generate the hash and the next parameters are used to generate the hash.
	 *
	 * @param string $algo
	 * @param string $_
	 */
	public function __construct($algo, $_)
	{
		$this->components = func_get_args();
		$this->algo       = array_shift($this->components);
	}
	
	/**
	 * Returns the identifier for the algorithm used to generate the hash.
	 *
	 * @return string
	 */
	public function getAlgo()
	{
		return $this->algo;
	}
	
	/**
	 * Generates a checksum and returns it. Please note that this method does not
	 * cache it's result. So running it several times may result in costly
	 * operations.
	 *
	 * @return Checksum
	 * @throws Exception
	 */
	public function hash()
	{
		$components   = $this->components;
		
		/*
		 * Reconstruct the original signature with the data we have about the
		 * source application to verify whether the apps are the same, and
		 * should therefore be granted access.
		 */
		switch (strtolower($this->algo)) {
			case 'sha512':
				$calculated = hash('sha512', implode(self::SEPARATOR, array_filter($components)));
				break;
			default:
				throw new Exception('Invalid algorithm', 400);
		}
		
		return new Checksum($this->algo, $calculated);
	}
}
