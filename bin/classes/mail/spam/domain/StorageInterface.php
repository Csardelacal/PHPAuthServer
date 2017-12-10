<?php namespace mail\spam\domain;

interface StorageInterface
{
	
	/*
	 * These constants allow the system to differentiate between IP and hostname
	 * when handling data, making it easy for the system to store the data while
	 * maintaining a good readability.
	 */
	const TYPE_HOSTNAME  = 0x00;
	const TYPE_IP        = 0x10;
	
	/**
	 * 
	 */
	const LIST_BLACKLIST = 'blacklist';
	const LIST_WHITELIST = 'whitelist';
	
	/**
	 * Returns whether a host was found in the blacklist. Please note that the
	 * system provides the IPs as base 64 encoded strings to make it easy to store.
	 * 
	 * @param string $host
	 */
	function isBlacklisted($host);
	
	/**
	 * Returns whether a host was found in the blacklist. Please note that the
	 * system provides the IPs as base 64 encoded strings to make it easy to store.
	 * 
	 * @param string $host
	 */
	function isWhitelisted($host);
}
