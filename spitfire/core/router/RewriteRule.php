<?php namespace spitfire\core\router;

use Closure;

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
 * A route is a class that rewrites a URL path (route) that matches a
 * route or pattern (old_route) into a new route that the system can 
 * use (new_route) to handle the current request.
 * 
 * A Route will only accept Closures, Responses or Paths (including arrays that
 * can be interpreted as Paths by the translation class) as the target.
 * 
 * @todo Define translate class for array to Path translation
 * @todo Define parameter class to replace inside Paths
 * @author César de la Cal <cesar@magic3w.com>
 */
abstract class RewriteRule
{
	/* These constants are meant for evaluating if a request should be answered 
	 * depending on if the request is done via HTTP(S). This is especially useful
	 * when your application wants to enforce HTTPS for certain requests.
	 */
	const PROTO_HTTP    = 0x01;
	const PROTO_HTTPS   = 0x02;
	const PROTO_ANY     = 0x03;
	
	/* These constants are intended to allow routes to react differently depending
	 * on the METHOD used to issue the request the server is receiving. Spitfire
	 * accepts any of the standard GET, POST, PUT or DELETE methods.
	 */
	const METHOD_GET    = 0x01;
	const METHOD_POST   = 0x02;
	const METHOD_PUT    = 0x04;
	const METHOD_DELETE = 0x08;
	const METHOD_HEAD   = 0x10;
	
	/**
	 * This var holds a reference to a route server (an object containing a pattern
	 * to match virtualhosts) that isolates this route from the others.
	 * 
	 * @var Routable
	 */
	private $server;
	private $pattern;
	private $patternStr;
	private $newRoute;
	private $method;
	private $protocol;
	
	/**
	 * A route is a pattern Spitfire uses to redirect an URL to something else.
	 * It can 'redirect' (without it being a 302) a request to a new URL, it can
	 * directly send back a response or assign a custom controller, action and 
	 * object to the request.
	 * 
	 * @param Routable $server The server this route belongs to
	 * @param string $pattern
	 * @param Closure|ParametrizedPath|array $new_route
	 * @param string $method
	 * @param int    $proto
	 */
	public function __construct(Routable$server, $pattern, $new_route, $method, $proto = Route::PROTO_ANY) {
		$this->server    = $server;
		$this->newRoute  = $new_route;
		$this->method    = $method;
		$this->protocol  = $proto;
		
		$this->patternStr = $pattern;
		$this->pattern    = URIPattern::make($pattern);
	}
	
	/**
	 * Checks whether a certain method applies to this route. The route can accept
	 * as many protocols as it wants. The protocols are converted to hex integers
	 * and are AND'd to check whether the selected protocol is included in the 
	 * list of admitted ones.
	 * 
	 * @param string|int $method
	 * @return boolean
	 */
	public function testMethod($method) {
		if (!is_numeric($method)) {
			switch ($method){
				case 'GET' :   $method = self::METHOD_GET; break;
				case 'POST':   $method = self::METHOD_POST; break;
				case 'HEAD':   $method = self::METHOD_HEAD; break;
				case 'PUT' :   $method = self::METHOD_PUT; break;
				case 'DELETE': $method = self::METHOD_DELETE; break;
			}
		}
		return $this->method & $method;
	}
	
	/**
	 * Tests whether the requested protocol (HTTPS or not) is accepted by this
	 * route. We use once again binary masks to test the protocol. This means that
	 * we can either use HTTP (01), HTTPS (10) or both (11) which will translate
	 * into an integer 1, 2 and 3.
	 *
	 * This way the user can quickly decide whether he wants to use any or both
	 * of them to match a route.
	 * 
	 * @param boolean $protocol
	 * @return boolean
	 */
	public function testProto($protocol) {
		if (!is_int($protocol)) {
			$protocol = ($protocol && $protocol != 'off')? Route::PROTO_HTTPS : Route::PROTO_HTTP;
		}
		return $this->protocol & $protocol;
	}
	
	public function test($URI, $method, $protocol) {
		try {
			#First we test the URI. Since the URI will throw an exception, we need 
			#to catch it and return false.
			$this->pattern->test($URI);
			return $this->testMethod($method) && $this->testProto($protocol);
		}
		catch (RouteMismatchException$e) {
			return false;
		}
	}
	
	/**
	 * 
	 * @return URIPattern
	 */
	public function getTarget() {
		return $this->newRoute;
	}
	
	/**
	 * 
	 * @return URIPattern
	 */
	public function getSource() {
		return $this->pattern;
	}
	
	/**
	 * 
	 * @todo Server parameter should be a Parameter set provided by the server IMO
	 * Need to check whether the server itself could have any impact on it
	 */
	abstract public function rewrite($URI, $method, $protocol, $server);
}
