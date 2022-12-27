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
	
	public function __construct(StorageInterface $reader)
	{
		$this->reader = $reader;
	}
	
	/**
	 *
	 * @param Domain $domain
	 * @return boolean True if the domain is blocked, false if the domain is accepted
	 */
	public function check(Domain$domain, $nxdomainfail = true)
	{
		
		/*
		 * If we got to the top of the resolution, we will stop. This is a recursive
		 * function, and this is the exit condition.
		 */
		if (empty($domain->getPieces())) {
			return false;
		}
		
		if ($this->reader->isWhitelisted($domain)) {
			return false;
		}
		
		if ($this->reader->isBlacklisted($domain)) {
			return true;
		}
		
		/*
		 * If one or more of the IP addresses that process this host's MX, then we
		 * return a negative result for this server.
		 */
		$mx = IP::mx($domain);
		$a  = IP::a($domain);
		
		/**
		 * If the system is required to check for the integrity of the domain name, the
		 * system should check whether the a or mx records exist.
		 */
		if ($nxdomainfail && ($mx->isEmpty() || $a->isEmpty())) {
			return true;
		}
		
		$ipban = $mx->add($a)->reduce(function ($p, IP$e) {
			do {
				/*
				 * If the IP was whitelisted, then we return false. It is implied that
				 * the IP can be safely operated.
				 */
				if ($this->reader->isWhitelisted($e)) {
					return false;
				}
				
				/*
				 * Check whether the IP itself was blacklisted.
				 */
				if ($p || $this->reader->isBlacklisted($e)) {
					return true;
				}
			}
			while ($e = $e->getParentSubnet());
			
			/*
			 * We default to the domain being okay if the application was unable to
			 * find any indication that the IP for the email server is being blocked.
			 */
			return false;
		}, false);
		
		if ($ipban) {
			return true;
		}
		
		if (!$domain->getParent()) {
			return false;
		}
		
		/*
		 * Check if the domain itself is blacklisted, and if this is not the case,
		 * then we check if the parent domain can be used to determine whether the
		 * domain is or not blacklisted.
		 */
		if ($this->check($domain->getParent(), false)) {
			return true;
		}
		
		/**
		 * Sometimes temporary email providers will use a single mail exchange
		 * domain coupled with several IPs. In this case, banning the mx server
		 * will do the trick.
		 *
		 * @todo we should record the mx server IPs for use in a domain structure so we
		 * can associate bad apples.
		 */
		$mxDomain = Domain::mx($domain);
		
		/*
		 * If we don't have a record for the domain to send email to, we will make
		 * the validation depend on NXDOMAINFAIL which will indicate whether the
		 * validation should fail due to an unavailable DNS record
		 */
		if ($mxDomain === false) {
			return $nxdomainfail;
		}
		
		return $mxDomain->reduce(function ($c, Domain$e) {
			return $c || $this->reader->isBlacklisted($e);
		}, false);
	}
}
