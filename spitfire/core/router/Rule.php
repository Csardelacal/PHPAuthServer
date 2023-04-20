<?php namespace spitfire\core\router;

use spitfire\exceptions\PrivateException;

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
 * The router\rule class allows the application to define both routes and 
 * redirections, since both inherit from it.
 * 
 * The request path finder will manage the nice-ness. And therefore will allow
 * spitfire to sort the requests by priority. Please note that the less nice a 
 * route / redirection is the sooner it will be used.
 * 
 * Also note that, depending on the task - the sorter may use other factors than
 * the nice-ness to determine whether a path finder has priority over another, 
 * specifically it's type will be very important.
 */
abstract class Rule
{
	
	const NICENESS_MIN = 0x00;
	const NICENESS_MAX = 0xff;
	
	/**
	 * The default niceness of a route that's intended to catch all the traffic.
	 * Please note that we do not use the nicest value for the default catch-all
	 * to provide additional fallback.
	 * 
	 * e.g. A system that shows a user-profile if the username doesn't collide 
	 * with a controller name. Obviously, a user should not be able to override
	 * a site's section with their username.
	 */
	const NICENESS_CATCHALL = 0xf9;

	/**
	 * Niceness is a term inherited from the Linux / Unix process management. 
	 * Every route is created as a high priority route with 0 niceness, but you
	 * can let your route give precedence to the rest.
	 * 
	 * Please note that niceness (0xff - 0x05) and above is reserved to default 
	 * routes and creating a route THAT nice may result in it never being used.
	 *
	 * @var int 
	 */
	private $niceness = 0x00;
	
	/**
	 * Defines the niceness of the rule. This allows the application to define 
	 * what priority the system gives to the rule.
	 * 
	 * As opposed to raising priority, niceness allows a rule to give precedence
	 * to the other rules.
	 * 
	 * @param int $niceness
	 * @return Rule
	 * @throws PrivateException
	 */
	public function setNiceness($niceness) {
		
		/*
		 * Since niceness can only have values ranging between (0 and 255) we will
		 * verify that these are not exceeded. Accepting values other than these 
		 * could lead to broken sorting and therefore faulty behaviour.
		 */
		if ($niceness > self::NICENESS_MAX || $niceness < self::NICENESS_MIN) {
			throw new PrivateException('Niceness is out of range', 1705161724);
		}
		
		#If the niceness was actually fine, then we continue.
		$this->niceness = $niceness;
		
		#Make this setter fluent
		return $this;
	}
	
	/**
	 * Returns the niceness score for the current rule. This allows the router 
	 * to sort them appropriately when routing or de-routing.
	 * 
	 * @return int
	 */
	public function getNiceness() {
		return $this->niceness;
	}
	
	/**
	 * Depending on whether this object is a Route or a Redirection it will return
	 * a route reverser or a redirection (that reverses the current one) respectively.
	 * 
	 * Please note that if a route provides no reverser (alas. no way to undo 
	 * itself) the method may return null and therefore require filtering.
	 * 
	 * @return reverser\RouteReverserInterface|Redirection|null
	 */
	public abstract function getReverser();
	
	/**
	 * This method allows a rule to rewrite the request it's receiving. This can
	 * result in either a Path, a Response object or a string (in the case of 
	 * redirections).
	 * 
	 * @param string $URI The URI being requested
	 * @param int    $method The HTTP method (GET / POST / PUT)
	 * @param int    $protocol Whether the request is HTTP or HTTPS
	 * @param Server $server The server the request is being routed through
	 * @return \spitfire\core\Path|\spitfire\core\Response|string|false
	 */
	public abstract function rewrite($URI, $method, $protocol, Server$server);
	
}