<?php namespace mail\spam\domain;

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

class SpamDomainTester
{
	
	/**
	 *
	 * @var StorageInterface
	 */
	private $reader;
	
	public function __construct(StorageInterface $reader) {
		$this->reader = $reader;
	}
	
	/**
	 * 
	 * @param Domain $domain
	 * @return boolean True if the domain is blocked, false if the domain is accepted
	 */
	public function check(Domain$domain, $nxdomainfail = true) {
		
		/*
		 * If we got to the top of the resolution, we will stop. This is a recursive
		 * function, and this is the exit condition.
		 */
		if(empty($domain->getPieces())) {
			return true;
		}
		
		/*
		 * If one or more of the IP addresses that process this host's MX, then we
		 * return a negative result for this server.
		 */
		$mx = IP::mx($domain);
		$ipban = !$mx? $nxdomainfail : $mx->reduce(function ($p, IP$e) { 
			do {
				/*
				 * If the IP was whitelisted, then we return false. It is implied that 
				 * the IP can be safely operated.
				 */
				if ($this->reader->isWhitelisted($e)) { return false; }

				/*
				 * Check whether the IP itself was blacklisted.
				 */
				if ($p || $this->reader->isBlacklisted($e)) { return true; }
			} 
			while ($e = $e->getParentSubnet());
			
			/*
			 * We default to the domain being okay if the application was unable to
			 * find any indication that the IP for the email server is being blocked.
			 */
			return false;
		}, false);
		
		/*
		 * Check if the domain itself is blacklisted, and if this is not the case,
		 * then we check if the parent domain can be used to determine whether the
		 * domain is or not blacklisted.
		 */
		return 
			!$this->reader->isWhitelisted($domain) &&
			($ipban || $this->reader->isBlacklisted($domain) || $this->check($domain->getParent(), false));
	}

}