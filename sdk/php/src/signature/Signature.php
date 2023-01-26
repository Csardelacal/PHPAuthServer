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
class Signature
{
	
	/**
	 * A signature hosts several pieces of information (depending on the request,
	 * 4 to 6 elements) and therefore needs a separator that also needs to not
	 * appear within the data.
	 *
	 * Since the information is only made up of alphanumeric characters (a-z,0-9)
	 * we can ensure that the data can be separated by colons.
	 */
	const SEPARATOR_SIGNATURE = ':';
	
	/**
	 * A signature may contain several contexts. Since this data is an Array-type
	 * kind of data, we need a separator for it to work in a string environment.
	 */
	const SEPARATOR_CONTEXT = ',';
	
	/**
	 * Indicates the hashing algorithm used to generate the hash for the signature,
	 * this should be strong enough to prevent a user from generating random
	 * collissions.
	 *
	 * @var string
	 */
	private $algo;
	
	/**
	 * The source application. This is the application signing the request, therefore,
	 * the secret used to generate the signature will ALWAYS be the one for this
	 * application.
	 *
	 * @var string
	 */
	private $src;
	
	/**
	 * The source's secret. This is generated during creation of the application
	 * and should never be transmitted.
	 *
	 * @var string
	 */
	private $secret;
	
	/**
	 * The App ID of the application that the request is meant for. This is used
	 * to query the server for public application data, like it's public URL or
	 * name - therefore allowing the app to present some basic data to the user
	 * about the remote application before connecting.
	 *
	 * When this is sent in a request without the appropriate context, it is used
	 * to authenticate the source against the target application. This way, the
	 * target app can add context as a _GET parameter and check whether the
	 * given contexts have been granted to the source application.
	 *
	 * This is not sufficient for context granting though. When a context is to be
	 * granted, the roles are reversed. The granting app becomes the source, and
	 * the grantee becomes the target.
	 *
	 * @var string
	 */
	private $target;
	
	/**
	 * Contexts are used in cross application communication to grant certain
	 * privileges. When an application wishes to exchange data, it will request
	 * access to certain parts of the remote application by requesting access to
	 * specific contexts.
	 *
	 * @var string[]
	 */
	private $context;
	
	private $expires;
	
	/**
	 * The salt is a random string attached to every signature, which makes it
	 * hard for an attacker to forge a request. The salt is mandatory and mustn't
	 * be empty for a request to be valid.
	 *
	 * @var string
	 */
	private $salt;
	
	/**
	 * This is the final, calculated checksum for this signature. A checksum object
	 * will contain the combination of algo and result of the sum operation.
	 *
	 * If the value of the checksum is null, it has not yet been calculated.
	 *
	 * @var Checksum|null
	 */
	private $checksum;
	
	/**
	 *
	 * @param string|null $algo
	 * @param int|string $src
	 * @param string|null $secret
	 * @param int|string $target
	 * @param string[] $context
	 * @param int $expires
	 * @param string|null $salt
	 * @param Checksum|null $hash
	 */
	public function __construct($algo, $src, $secret, $target, $context, $expires, $salt = null, Checksum $hash = null)
	{
		$this->algo = $algo?: Hash::ALGO_DEFAULT;
		$this->src = $src;
		$this->secret = $secret;
		$this->target = $target;
		$this->context = $context;
		$this->expires = $expires;
		$this->salt = $salt;
		$this->checksum = $hash;
	}
	
	public function getAlgo()
	{
		return $this->algo;
	}
	
	public function getSrc()
	{
		return $this->src;
	}
	
	public function getTarget()
	{
		return $this->target;
	}
	
	public function getContext()
	{
		return $this->context;
	}
	
	public function getSalt()
	{
		
		if (!$this->salt) {
			$this->salt = substr(base64_encode(random_bytes(50)), 0, 50);
		}
		
		return $this->salt;
	}
	
	public function isExpired()
	{
		return $this->expires < time();
	}
	
	public function getExpiration()
	{
		return $this->expires === null? time() + 600 : $this->expires;
	}
	
	/**
	 * Calculates the checksum needed to verify the signature while keeping the
	 * secret hidden from curious eyes.
	 *
	 * @return Checksum
	 * @throws PrivateException
	 */
	public function checksum()
	{
		
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
			$hash = new Hash($this->algo, $this->src, $this->target, $this->secret, $this->context, $this->getExpiration(), $this->getSalt());
			$this->checksum = $hash->hash();
		}
		
		return $this->checksum;
	}
	
	public function salt($salt)
	{
		$this->salt = $salt;
		$this->checksum = null;
		return $this;
	}
	
	public function setHash(Checksum$hash)
	{
		$this->checksum = $hash;
		return $this;
	}
	
	public function setSecret($secret)
	{
		$this->secret = $secret;
		$this->checksum = null;
		return $this;
	}
	
	public function __toString()
	{
		return implode(self::SEPARATOR_SIGNATURE, array_filter([
			$this->algo,
			$this->src,
			$this->target,
			$this->context,
			$this->getExpiration(),
			$this->getSalt(),
			$this->checksum()->hash()
		]));
	}
	
	/**
	 * Creates a new signature. This method will use the default hashing mechanism
	 * and generate a valid signature that the system can use.
	 *
	 * @todo This should be moved to a helper. Not static.
	 * @param string $src
	 * @param string $target
	 * @param string[] $context
	 * @return Signature
	 */
	public static function make($src, $secret, $target, $context)
	{
		return new Signature(Hash::ALGO_DEFAULT, $src, $secret, $target, $context, time() + 3600);
	}
}
