<?php namespace mail\domain;

/**
 * 
 */
interface WriterInterface
{
	
	/**
	 * Introduces an entry to the white or blacklist of email domains. Please note,
	 * that IPs are again provided as base64 encoded strings.
	 * 
	 * Also, note that your implementation should automatically expire the IP after
	 * a given time. IPs are not permanent after all. A generous expiry should do
	 * the job though (something along the lines of a year).
	 * 
	 * @param string $host
	 * @param int    $list
	 * @param int    $type
	 * @param int    $subdomains
	 * @param string $reason
	 */
	function addEntry($host, $list, $type, $subdomains, $reason);
}

