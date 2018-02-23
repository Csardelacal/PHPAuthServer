<?php namespace signature;

use spitfire\exceptions\PrivateException;
use spitfire\exceptions\PublicException;

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
class Signature
{
	
	const SEPARATOR_SIGNATURE = ':';
	
	/**
	 * A signature may contain several contexts. Since this data is an Array-type
	 * kind of data, we need a separator for it to work in a string environment.
	 */
	const SEPARATOR_CONTEXT = ',';
	
	private $algo;
	
	private $src;
	
	private $secret;
	
	private $target;
	
	private $context;
	
	private $salt;
	
	/**
	 *
	 * @var Checksum
	 */
	private $checksum;
	
	/**
	 * 
	 * @param string $algo
	 * @param string $src
	 * @param string $target
	 * @param string $context
	 * @param string $salt
	 * @param Checksum $hash
	 */
	public function __construct($algo, $src, $secret, $target, $context, $salt = null, Checksum$hash = null) {
		$this->algo = $algo?: Hash::ALGO_DEFAULT;
		$this->src = $src;
		$this->secret = $secret;
		$this->target = $target;
		$this->context = $context;
		$this->salt = $salt;
		$this->checksum = $hash instanceof Checksum || !$hash? $hash : new Checksum($this->algo, $hash);
	}
	
	public function getAlgo() {
		return $this->algo;
	}
	
	public function getSrc() {
		return $this->src;
	}
	
	public function getTarget() {
		return $this->target;
	}
	
	public function getContext() {
		return $this->context;
	}
	
	public function getSalt() {
		
		if (!$this->salt) {
			$this->salt = substr(base64_encode(random_bytes(50)), 0, 50);
		}
		
		return $this->salt;
	}
	
	/**
	 * Calculates the checksum needed to verify the signature while keeping the 
	 * secret hidden from curious eyes.
	 * 
	 * @return Checksum
	 * @throws PrivateException
	 */
	public function checksum() {
		
		/**
		 * In the event of the signature missing either the secret or the pre-calculated
		 * checksum (this is the case for signatures that were sent from remote
		 * sources) we will be unable to generate a proper sum and need to stop 
		 * the execution.
		 */
		if (!$this->checksum && !$this->secret) {
			throw new PrivateException('Incomplete signature. Cannot be hashed', 1802082113);
		}
		
		/**
		 * If the system has no pre-calculated checksum we will create a hash to 
		 * calculate the checksum.
		 */
		if (!$this->checksum) {
			$hash = new Hash($this->algo, $this->src, $this->target, $this->secret, implode(self::SEPARATOR_CONTEXT, $this->context), $this->getSalt());
			$this->checksum = $hash->hash();
		}
		
		return $this->checksum;
	}
	
	public function salt($salt) {
		$this->salt = $salt;
		$this->checksum = null;
		return $this;
	}
	
	public function setHash(Checksum$hash) {
		$this->checksum = $hash;
		return $this;
	}
		
	/**
	 * Splits up a signature sent from a remote server and extracts the data 
	 * provided by it. The system can then use the hash to compare it to a existing
	 * dataset.
	 * 
	 * @param string $from
	 * @return Signature
	 * @throws PublicException
	 */
	public static function extract($from) {
		$signature = explode(self::SEPARATOR_SIGNATURE, $from);
		$context   = [];
		
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
				$context = explode(self::SEPARATOR_CONTEXT, $contextstr);
				break;
			default:
				throw new PublicException('Invalid signature', 400);
		}
		
		return new self($algo, $src, null, $target, $context, $salt, new Checksum($algo, $hash));
	}
	
	/**
	 * Creates a new signature. This method will use the default hashing mechanism
	 * and generate a valid signature that the system can use.
	 * 
	 * @param string $src
	 * @param string $target
	 * @param string $context
	 * @return Signature
	 */
	public static function make($src, $secret, $target = null, $context = null) {
		return new Signature(Hash::ALGO_DEFAULT, $src, $secret, $target, $context);
	}
	
}