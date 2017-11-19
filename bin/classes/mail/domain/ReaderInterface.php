<?php namespace mail\domain;

interface ReaderInterface
{
	
	/*
	 * These constants allow the system to differentiate between IP and hostname
	 * when handling data, making it easy for the system to store the data while
	 * maintaining a good readability.
	 */
	const TYPE_HOSTNAME  = 0x00;
	const TYPE_IP        = 0x10;
	const TYPE_IPV4      = 0x11;
	const TYPE_IPV6      = 0x12;
	
	/**
	 * 
	 */
	const LIST_BLACKLIST = 0x00;
	const LIST_WHITELIST = 0x01;
	
	/**
	 * Returns whether a host was found in the blacklist. Please note that the
	 * system provides the IPs as base 64 encoded strings to make it easy to store.
	 * 
	 * @param string $host
	 * @param int    $type
	 */
	function isBlacklisted($host, $type = ReaderInterface::TYPE_HOSTNAME);
	
	/**
	 * Returns a collection of elements that the system should be rechecking to 
	 * ensure that the IP list is up to date.
	 * 
	 * Refresh time can generally be rather generous, since server IPs don't
	 * change very often and are generally within a given block for big email
	 * servers.
	 * 
	 * Spam servers will often come and go, but these will be better caught by
	 * rechecking their blacklisted status on usage.
	 * 
	 * @param int $timestamp The timestamp since the domain was not refreshed
	 * @return \spitfire\core\Collection
	 */
	function getDomainsRefreshedBefore($timestamp);
	
}
