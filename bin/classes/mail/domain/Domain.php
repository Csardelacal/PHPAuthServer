<?php namespace mail\domain;

class Domain
{
	
	/**
	 * An array of TLD. These allow the application to detect when the only part 
	 * left of the domain name is a TLD.
	 * 
	 * This is due to how PHPAS parses domains. It will try to chain up to the 
	 * TLD whether the domain is acceptable.
	 *
	 * @var string[]
	 */
	public static $tld = ['org', 'co', 'uk', 'com', 'ca', 'au', 'es', 'de', 'ly', 'ie', 'fr', 'us', 'biz', 'tk'];
	
	
	/**
	 * The array containing the domain name split by dot characters (.).
	 *
	 * @var string[]
	 */
	private $pieces;
	
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
	
	/**
	 * 
	 * @param string|string[] $pieces
	 * @param ReaderInterface $reader
	 * @param WriterInterface $writer
	 */
	public function __construct($pieces, ReaderInterface $reader, WriterInterface $writer) {
		$this->pieces = is_string($pieces)? explode('.', $pieces) : $pieces;
		$this->reader = $reader;
		$this->writer = $writer;
	}
	
	public function isBanned() {
		$host = implode('.', $this->pieces);
		
		/*
		 * If we got to the point of being only left with a TLD we cannot verify
		 * whether the DNS records for it exist.
		 */
		if(!$this->isTLD()) {
			$ips = $this->getIpAddresses($host);
			if ($this->reader->isBlacklisted($ips, ReaderInterface::TYPE_IP)) { return true; }
		}
		
		if ($this->reader->isBlacklisted($host)) {
			return true;
		} 
		else {
			$parent = new Domain(array_slice($this->pieces, 1));
			return $parent->isBanned();
		}
	}
	
	/**
	 * If a domain is a TLD, which implies that it was 
	 * 
	 * @return boolean
	 */
	public function isTLD() {
		
		$pieces = collect($this->pieces);
		
		return $pieces->count() < 3 && $pieces->reduce(function ($e, $p) { 
			return $p && strlen($e) <= 3 && in_array($e, Domain::$tld);
		}, true);
	}
	
	public function getIpAddresses($hostname) {
		if(!getmxrr($hostname, $mxhosts)) { return true; }
		return collect($mxhosts)->each(function ($e) { return base64encode(inet_pton(gethostbyname($e))); });
	}
	
	public function ban($subdomains, $reason) {
		$this->writer->addEntry(implode('.', $this->pieces), 'blacklist', ReaderInterface::TYPE_HOSTNAME, $subdomains, $reason);
		
		$this->getIpAddresses(implode('.', $this->pieces))->each(function ($e) use ($reason) {
			$this->writer->addEntry($e, 'blacklist', ReaderInterface::TYPE_IP, null, $reason);
		});
	}
	
	public function whitelist($subdomains, $reason) {
		$this->writer->addEntry(implode('.', $this->pieces), 'whitelist', ReaderInterface::TYPE_HOSTNAME, $subdomains, $reason);
		
		$this->getIpAddresses(implode('.', $this->pieces))->each(function ($e) use ($reason) {
			$this->writer->addEntry($e, 'whitelist', ReaderInterface::TYPE_IP, null, $reason);
		});
	}

}
