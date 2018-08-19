<?php

use spitfire\cache\MemcachedAdapter;

class IP
{
	/**
	 * This is awful. And I wish I wasn't making this kind of compromise.
	 */
	public static function makeLocation() {
		$ip = isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR']: $_SERVER['REMOTE_ADDR'];
		
		/*
		 * Prepare the memcached adapter so we don't force the API.
		 */
		$m = new MemcachedAdapter();
		$m->setTimeout(86400 * 30);
		
		return $m->get('ip_' . $ip, function() use ($ip) {
			/*
			 * Extract the IP from the request
			 */
			$r  = file_get_contents(sprintf('http://api.ipstack.com/%s?access_key=%s', $ip, \spitfire\core\Environment::get('ipstack.key')));

			/*
			 * Check if the request returned anything
			 */
			if (!$r) { return false; }
			if (!strstr($http_response_header[0], '200')) { return false; }

			/*
			 * If everything went well with the request. We check the response and 
			 * decode it with JSON
			 */
			$data = json_decode($r);
			
			return $data;
		});
	}
	
}

