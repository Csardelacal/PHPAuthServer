<?php namespace spitfire\core\router;

use spitfire\core\Path;
use spitfire\core\Response;
use spitfire\core\router\reverser\BaseServerReverser;

/**
 * A server in Spitfire's router is a certain virtual host the application is 
 * listening to. Imagine your application replying to different domain names with
 * different responses.
 * 
 * This allows a Spitfire based application to, for example, manage all the 
 * GTLD for your application like yourapp.com, yourapp.es or yourapp.de
 */
class Server extends Routable
{
	
	private $router;
	private $pattern;
	private $parameters;
	
	public function __construct($pattern, Router$router) {
		$array = explode('.', $pattern);
		array_walk($array, function (&$pattern) {$pattern= new Pattern($pattern);});
		$this->pattern = $array;
		
		$this->router  = $router;
		
		parent::__construct();
	}
	
	/**
	 * 
	 * @throws RouteMismatchException If the path does not match
	 * @param array $pattern
	 * @param array $array
	 */
	protected function patternWalk($pattern, $array) {
		$parameters = Array();
		
		foreach ($pattern as $p) {
			$parameters = array_merge($parameters, $p->test(array_shift($array)));
		}
		
		$this->parameters = new Parameters();
		$this->parameters->addParameters($parameters);
		
		return $this->parameters;
	}
	
	public function test($servername) {
		$array = explode('.', $servername);
		
		try {
			return $this->patternWalk($this->pattern, $array);
		} catch(RouteMismatchException $e) {
			return false;
		}
	}
	
	public function rewrite($server, $url, $method, $protocol, $extension = null) {
		#If the server doesn't match we don't continue
		if (!($params = $this->test($server))) { return false; }
		
		#Combine routes from the router and server
		$routes = array_merge(
				  $this->getRedirections()->toArray(), $this->router->getRedirections()->toArray(),
				  $this->getRoutes()->toArray(), $this->router->getRoutes()->toArray()
		);
		
		#Test the routes
		foreach ($routes as $route) { /*@var $route Route*/
			
			#Verify whether the route is valid at all
			if (!$route->test($url, $method, $protocol)) { continue; }
			
			#Check whether the route can rewrite the request
			$rewrite = $route->rewrite($url, $method, $protocol, $params, $extension);

			if ( $rewrite instanceof Path || $rewrite instanceof Response) { return $rewrite; }
			if ( $rewrite !== false)         { $url = $rewrite; }
		}
	}
	
	public function getParameters() {
		if ($this->parameters === null) { $this->test(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'); }
		if ($this->parameters === false) { return new Parameters(); }
		return $this->parameters;
	}
	
	public function getReverser() {
		return new BaseServerReverser($this->pattern, $this);
	}

}
