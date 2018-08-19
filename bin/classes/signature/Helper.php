<?php namespace signature;

use spitfire\exceptions\PublicException;

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

/**
 * The signature helper provides convenience methods to easily and quickly generate
 * and parse Signatures sent from or to remote servers.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class Helper
{
	
	private $db;
	
	
	public function __construct($db) {
		$this->db = $db;
	}
	
	/**
	 * Splits up a signature sent from a remote server and extracts the data 
	 * provided by it. The system can then use the hash to compare it to a existing
	 * dataset.
	 * 
	 * @todo This should be moved to a helper. Not static.
	 * @param string $from
	 * @return Signature
	 * @throws PublicException
	 */
	public function extract($from) {
		$signature = explode(Signature::SEPARATOR_SIGNATURE, $from);
		$context   = [];
		
		switch(count($signature)) {
			case 4: //TODO: Remove - DEPRECATED
				list($algo, $src, $salt, $hash) = $signature;
				$target = null;
				break;
			case 5:
				list($algo, $src, $expires, $salt, $hash) = $signature;
				$target = null;
				break;
			case 6:
				list($algo, $src, $target, $expires, $salt, $hash) = $signature;
				break;
			case 7:
				list($algo, $src, $target, $contextstr, $expires, $salt, $hash) = $signature;
				$context = explode(Signature::SEPARATOR_CONTEXT, $contextstr);
				break;
			default:
				throw new PublicException('Invalid signature', 400);
		}
		
		return new Signature($algo, $src, null, $target, $context, $expires, $salt, new Checksum($algo, $hash));
	}
	
	/**
	 * 
	 * @param type $e
	 * @return type
	 * @throws PublicException
	 */
	public function verify($e = null) {
		
		/*
		 * Reconstruct the original signature with the data we have about the 
		 * source application to verify whether the apps are the same, and
		 * should therefore be granted access.
		 */
		$signature = $this->extract($e === null? $_GET['signature'] : $e);

		/*
		 * PHPAuthServer requires signatures to be unexpired. The server issuing
		 * the signature can freely decide how long they want the signature to
		 * be valid.
		 * 
		 * It is unlikely that the system could be man-in-the-middle attacked,
		 * but it is possible that a signature may leak during a server error
		 * or due to human error. In this case, an expiry of 5 minutes gives 
		 * most servers ample time to process the request but an attacker will
		 * have a hard time forging an attack that will be effective.
		 */
		if ($signature->isExpired()) { 
			throw new PublicException('Expired signature', 400);
		}

		/**
		 * @var AuthAppModel The source application (the application requesting data)
		 */
		$src = db()->table('authapp')->get('appID', $signature->getSrc())->first(true);
		$tgt = db()->table('authapp')->get('appID', $signature->getTarget())->first();

		/*
		 * In order to verify that the signature is correct, we make a second signature
		 * that gets completed with the app secret of the source application.
		 */
		$check = $this->extract($e === null? $_GET['signature'] : $e)->setSecret($src->appSecret);

		if (!$check->checksum()->verify($signature->checksum())) {
			throw new PublicException('Hash failure', 403);
		}

		return [$signature, $src, $tgt];
	}
	
	/**
	 * Creates a new signature. This method will use the default hashing mechanism
	 * and generate a valid signature that the system can use.
	 * 
	 * @todo This should be moved to a helper. Not static.
	 * @param string $src
	 * @param string $target
	 * @param string $context
	 * @return Signature
	 */
	public function make($src, $secret, $target = null, $context = null) {
		return new Signature(Hash::ALGO_DEFAULT, $src, $secret, $target, $context, time() + 600);
	}
}