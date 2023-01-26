<?php namespace magic3w\phpauth\sdk\signature;

use spitfire\exceptions\PrivateException;

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
 * When a hash is executed, it should return a checksum. This is a combination
 * of the computed hash and the algorithm used to compute said hash.
 *
 * The algo is usually not very important, since the low collission rate will
 * usually prevent two hashes from being equal if they were generated with
 * different mechanisms. But, it makes it even harder for sums to be spoofed in
 * any way.
 *
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 * @todo Remove
 * @deprecated
 */
class Checksum
{
	
	/**
	 * The procedure used to calculate the checksum. Please note that if the
	 * algorithms missmatch, the application will throw an exception.
	 *
	 * @var string
	 */
	private $algo;
	
	/**
	 * The hashed sum. This contains a string that will get compared. If the two
	 * are different, the software will return false.
	 *
	 * @var string
	 */
	private $hash;
	
	/**
	 * Creates a new checksum result. You need to provide the algorithm, and the
	 * resulting hash.
	 *
	 * @param string $algo
	 * @param string $hash
	 */
	public function __construct($algo, $hash)
	{
		$this->algo = $algo;
		$this->hash = $hash;
	}
	
	/**
	 * Returns the name of the algorithm used to generate the hash.
	 *
	 * @return string
	 */
	public function getAlgo()
	{
		return $this->algo;
	}
	
	/**
	 * Returns the hash generated. Please note that the Checksum is no longer
	 * aware of the original string and therefore cannot recalculate the hash.
	 *
	 * @return string
	 */
	public function hash()
	{
		return $this->hash;
	}
	
	/**
	 * This method allows to compare two Hashverifiers. This method is symmetric,
	 * you can compare like $a->verify($b) and $b->verify($a) and they provide the
	 * exact same result.
	 *
	 * @param Checksum $hash
	 * @return bool
	 * @throws PrivateException
	 */
	public function verify(Checksum$hash)
	{
		
		/**
		 * If the algo is not matched - the application will throw an exception.
		 * It is not an acceptable behavior for the application to provide different
		 * algos to compare a checksum.
		 */
		if ($this->algo !== $hash->getAlgo()) {
			throw new PrivateException('Algorithm missmatch', 1802072349);
		}
		
		return $this->hash === $hash->hash();
	}
}
