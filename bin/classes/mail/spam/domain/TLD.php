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

class TLD
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
	public static $tld = [
		'org', 'co', 'uk', 'com', 'ca', 'au', 'es', 'de', 'ly', 'ie', 'fr', 'us', 
		'biz', 'tk', 'br', 'cat', 'cn', 'bv', 'bw', 'by', 'zm', 'zw', 'be', 'yt'
	];
	
	/**
	 * Some TLD are 
	 * 
	 * @param Domain $domain The domain to be tested
	 * @return boolean
	 */
	public static function isTLD(Domain $domain) {
		
		$pieces = collect($domain->getPieces());
		
		/*
		 * If there's only one piece left (this system assumes that if a system
		 * is inside a intranet and hostnames are like "server1", this system will
		 * assume that it's a TLD and just accept it)
		 */
		if ($pieces->count() === 1) {
			return true;
		}
		
		return $pieces->count() < 3 && $pieces->reduce(function ($p, $e) { 
			return $p && strlen($e) <= 3 && in_array($e, self::$tld);
		}, true);
	}
}