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

class IP
{
	private $ip;
	
	private $cidr;
	
	public function __construct($ip, $cidr = 0) {
		$this->ip = inet_pton($ip);
		$this->cidr = $cidr;
	}
	
	public function getIP() {
		return inet_ntop($this->cidr? $this->calculateSubnet() : $this->ip);
	}
	
	public function getSubnetCIDR() {
		return $this->cidr;
	}
	
	public function calculateSubnet() {
		$mask   = $this->cidr;
		$str    = $this->ip;
		$length = strlen($str);
		
		/*
		 * Every character of the string represents 8 bits of binary data. PHP is
		 * a bit quirky about how it handles binary strings, since it will (when
		 * extracting a single character) create a new string(1) instead of a char
		 * 
		 * This means that our application needs to extract the int value of the
		 * string's character, operate on it and place it back where it was.
		 */
		for ($i = 0; $i < $length; $i++) {
			 $shift   = max(0, min(8, $mask - $i * 8));
			 $str[$i] = chr(ord($str[$i]) & ((1 << ($shift)) - 1)); 
		}
		
		return $str;
	}
	
	public function getBase64() {
		if ($this->cidr == 0) {
			$cidr = strlen($this->ip) * 8;
		}
		else {
			$cidr = $this->cidr;
		}
		
		return base64_encode($this->cidr? $this->calculateSubnet() : $this->ip) . ';' . $cidr;
	}
	
	public function getParentSubnet() {
		/*
		 * Determine the previous CIDR. A 0 value CIDR implies that the software
		 * didn't care to determine the type of address.
		 */
		if ($this->cidr === 0) { $cidr = strlen($this->ip) * 8; }
		else                   { $cidr = $this->cidr; }
		
		/*
		 * Calculate a minimum CIDR. This depends on the type of address too. If 
		 * the IP is 32 bit (4 byte) we will enforce a mask that's 8 or more.
		 */
		$min = strlen($this->ip) === 4? 8 : 32;
		
		return $cidr > $min + 4? new IP(inet_ntop($this->ip), $cidr - 4) : null;
	}
	
	public function __toString() {
		if ($this->cidr == 0) {
			$cidr = strlen($this->ip) * 8;
		}
		else {
			$cidr = $this->cidr;
		}
		
		return sprintf('%s/%s', $this->getIP(), $cidr);
		
	}
	
	public static function fromBase64($str) {
		$pieces = explode(';', $str);
		$ip     = array_shift($pieces);
		$cidr   = array_shift($pieces)?: 0;
		
		return new IP(inet_ntop(base64_decode($ip)), $cidr);
	}
	
	/**
	 * 
	 * @todo This function currently only supports IPV4
	 * @param $hostname string
	 */
	public static function mx($hostname) {
		if ($hostname instanceof Domain) {
			$hostname = $hostname->getHostname();
		}
		
		if(!getmxrr($hostname, $mxhosts)) { return false; }
		return collect($mxhosts)->each(function ($e) { return new IP(gethostbyname($e)); });
	}
	
}