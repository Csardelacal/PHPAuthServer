<?php namespace mail\spam\domain;

use mail\spam\SpamRuleInterface;

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

class DomainSpamRule implements SpamRuleInterface
{
	
	/**
	 *
	 * @var ReaderInterface 
	 */
	private $reader;
	
	/**
	 *
	 * @var WriterInterface 
	 */
	private $writer;
	
	public function __construct(ReaderInterface $reader, WriterInterface $writer) {
		$this->reader = $reader;
		$this->writer = $writer;
	}
	
	public function test($emailAddress) {
		/*
		 * This is a recursive function, when a domain does not match the rules,
		 * the parent domain is tested.
		 */
		if ($emailAddress instanceof Domain) {
			$domain = $emailAddress;
		} 
		else {
			$pieces = explode('@', $emailAddress);
			$domain = new Domain(array_pop($pieces));
		}
		
		/*
		 * If we got to the point of being only left with a TLD we cannot verify
		 * whether the DNS records for it exist.
		 */
		if(TLD::isTLD($domain) || empty($domain->getPieces())) {
			return false;
		}
		
		/*
		 * If one or more of the IP addresses that process this host's MX, then we
		 * return a negative result for this server.
		 */
		$ipban = IP::mx($domain)->reduce(function ($p, $e) { return $p || $this->reader->isBlacklisted($e); }, false);
		if ($ipban) { return true; }
		
		/*
		 * Check if the domain itself is blacklisted, and if this is not the case,
		 * then we check if the parent domain can be used to determine whether the
		 * domain is or not blacklisted.
		 */
		if ($this->reader->isBlacklisted($domain)) {
			return true;
		} 
		else {
			return $this->test($domain->getParent());
		}
	}
	
	public function ban(Domain$domain, $subdomains, $reason) {
		$this->writer->addEntry($domain, ReaderInterface::LIST_BLACKLIST, $subdomains, $reason);
		
		$this->getIpAddresses(implode('.', $this->pieces))->each(function ($e) use ($reason) {
			$this->writer->addEntry($e, ReaderInterface::LIST_BLACKLIST, null, $reason);
		});
	}
	
	public function whitelist($subdomains, $reason) {
		$this->writer->addEntry(implode('.', $this->pieces), ReaderInterface::LIST_WHITELIST, ReaderInterface::TYPE_HOSTNAME, $subdomains, $reason);
		
		$this->getIpAddresses(implode('.', $this->pieces))->each(function ($e) use ($reason) {
			$this->writer->addEntry($e, ReaderInterface::LIST_WHITELIST, ReaderInterface::TYPE_IP, null, $reason);
		});
	}
	
	public function crontab() {
		$domains = $this->reader->getDomainsRefreshedBefore(time());
		
		$domains->each(function (Domain$e) {
			$list = $e->isBanned()? ReaderInterface::LIST_BLACKLIST : ReaderInterface::LIST_WHITELIST;
			$e->getIpAddresses(function ($s) use ($list) {
				$this->writer->addEntry($s, $list, ReaderInterface::TYPE_IP, null, 'Refresh performed by crontab');
			});
		});
	}

}