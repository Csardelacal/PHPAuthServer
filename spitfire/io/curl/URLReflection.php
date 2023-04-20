<?php namespace spitfire\io\curl;

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

/**
 * This class allows to retrieve database settings in a organized manner. This 
 * should make the transfer to environment based database settings much easier.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class URLReflection
{
	
	private $protocol;
	private $server;
	private $port;
	private $user;
	private $password;
	private $path;
	private $queryString;
	
	private static $defaults = [
		'scheme'   => 'https',
		'host'     => 'localhost',
		'port'     => null,
		'user'     => '',
		'pass'     => '',
		'path'     => '/',
		'query'    => '',
		'fragment' => ''
	];
	
	public function __construct($protocol, $server, $port, $user, $password, $path, $querystr) {
		$this->protocol = $protocol;
		$this->server = $server;
		$this->port   = $port === null? ($protocol === 'http'? 80 : 443) : $port;
		$this->user = $user;
		$this->password = $password;
		$this->path = $path;
		
		parse_str($querystr, $query);
		$this->queryString = $query;
	}
	
	public function getProtocol() {
		return $this->protocol;
	}
	
	public function getQueryString() {
		return $this->queryString;
	}
	
	public function setProtocol($protocol) {
		$this->protocol = $protocol;
		return $this;
	}
	
	public function setQueryString($queryString) {
		$this->queryString = $queryString;
		return $this;
	}
		
	public function getServer() {
		return $this->server;
	}
	
	public function getUser() {
		return $this->user;
	}
	
	public function getPassword() {
		return $this->password;
	}
	
	public function getPath() {
		return $this->path;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function setServer($server) {
		$this->server = $server;
		return $this;
	}
	
	public function setPort($port) {
		$this->port = $port;
		return $this;
	}
	
	public function setUser($user) {
		$this->user = $user;
		return $this;
	}
	
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}
	
	public function setPath($path) {
		$this->path = $path;
		return $this;
	}
	
	public function __toString() {
		$server = $this->server;
		$path   = $this->path;
		
		if (isset($this->user) && !empty($this->user)) {
			$server = $this->user . ':' . $this->password . '@' . $server;
		}
		
		if (isset($this->port) && !($this->protocol === 'https' && $this->port === 443) && !($this->protocol === 'http' && $this->port === 80)) {
			$server = $server . ':' . $this->port;
		}
		
		if (!empty($this->queryString)) {
			$path = $path . '?' . http_build_query($this->queryString);
		}
		
		return sprintf('%s://%s/%s', $this->protocol, $server, ltrim($path, '/'));
	}
	
	/**
	 * Reads the settings from a URL. Since October 2017 we're focusing on providing
	 * URLs to store database credentials, which allow in turn to store the DB
	 * settings outside of the application and on the server itself.
	 * 
	 * @todo Move to external URL parser
	 * @param Settings|string $url
	 * @return Settings
	 */
	public static function fromURL($url) {
		
		/*
		 * If the parameter provided is already a settings object, it will be 
		 * returned as is.
		 */
		if ($url instanceof self) { return $url; }
		
		return self::fromArray(parse_url($url));
	}
	
	public static function fromArray($arr) {
		$ops = $arr + self::$defaults;
		
		return new self(
			$ops['scheme'], 
			$ops['host'], 
			$ops['port'], 
			$ops['user'], 
			$ops['pass'], 
			$ops['path'], 
			$ops['query'],
			$ops['fragment']
		);
	}
	
}
