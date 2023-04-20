<?php namespace spitfire\storage\database;

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
class Settings
{
	
	private $driver;
	private $server;
	private $port;
	private $user;
	private $password;
	private $schema;
	private $prefix;
	private $encoding;
	
	private static $defaults = [
		'driver'   => 'mysqlpdo',
		'server'   => 'localhost',
		'port'     => null,
		'user'     => 'root',
		'password' => '',
		'schema'   => 'database',
		'prefix'   => '',
		'encoding' => 'utf8'
	];
	
	public function __construct($driver, $server, $port, $user, $password, $schema, $prefix, $encoding) {
		$this->driver = $driver;
		$this->server = $server;
		$this->port   = $port;
		$this->user = $user;
		$this->password = $password;
		$this->schema = $schema;
		$this->prefix = $prefix;
		$this->encoding = $encoding;
	}
	
	public function getDriver() {
		return $this->driver;
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
	
	public function getSchema() {
		return $this->schema;
	}
	
	public function getPrefix() {
		return $this->prefix;
	}
	
	public function getEncoding() {
		return $this->encoding;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function setDriver($driver) {
		$this->driver = $driver;
		return $this;
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
	
	public function setSchema($schema) {
		$this->schema = $schema;
		return $this;
	}
	
	public function setPrefix($prefix) {
		$this->prefix = $prefix;
		return $this;
	}
	
	public function setEncoding($encoding) {
		$this->encoding = $encoding;
		return $this;
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
		if ($url instanceof Settings) { return $url; }
		
		/*
		 * Extract the basic data
		 */
		$driver   = parse_url($url, PHP_URL_SCHEME)?: self::$defaults['driver'];
		$server   = parse_url($url, PHP_URL_HOST)  ?: self::$defaults['server'];
		$port     = parse_url($url, PHP_URL_PORT)  ?: self::$defaults['port']; 
		$user     = parse_url($url, PHP_URL_USER)  ?: self::$defaults['user'];
		$password = parse_url($url, PHP_URL_PASS)  ?: self::$defaults['password'];
		$schema   = parse_url($url, PHP_URL_PATH)  ?: self::$defaults['port'];
		
		/**
		 * Extract the data that was provided by the URL. Then we should be able 
		 * to easily retrieve the data it provides.
		 */
		parse_str(parse_url($url, PHP_URL_QUERY)?: '', $query);
		
		$prefix   = $query['prefix']  ?? self::$defaults['prefix'];
		$encoding = $query['encoding']?? self::$defaults['encoding'];
		
		return new Settings($driver, $server, $port, $user, $password, substr($schema, 1), $prefix, $encoding);
	}
	
	public static function fromArray($arr) {
		$ops = $arr + self::$defaults;
		
		return new Settings(
			$ops['driver'], 
			$ops['server'], 
			$ops['port'], 
			$ops['user'], 
			$ops['password'], 
			$ops['schema'], 
			$ops['prefix'],
			$ops['encoding']
		);
	}
	
}
