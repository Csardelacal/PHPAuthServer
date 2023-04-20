<?php namespace spitfire\core\router;

/**
 * Routers are tools that allow your application to listen on alternative urls and
 * attach controllers to different URLs than they would normally do. Please note
 * that enabling a route to a certain controller does not disable it's canonical
 * URL.
 * 
 * @author César de la Cal <cesar@magic3w.com>
 */
class Router extends Routable
{
	
	private $servers = Array();
	
	/**
	 * This rewrites a request into a Path (or in given cases, a Response). This 
	 * allows Spitfire to use the data from the Router to accordingly find a 
	 * controller to handle the request being thrown at it.
	 * 
	 * Please note that Spitfire is 'lazy' about it's routes. Once it found a valid
	 * one that can be used to respond to the request it will stop looking for
	 * another possible rewrite.
	 * 
	 * @todo The extension should be passed down to the servers (and therefore 
	 * the routes) to allow the routes to respond to different requests properly.
	 * 
	 * @param string $server
	 * @param string $route
	 * @param string $method
	 * @param string $protocol
	 * @return Path|Response
	 */
	public function rewrite ($server, $route, $method, $protocol) {
		#Ensures the base server is created
		$this->server();
		#Loop through the servers to find valid routes
		$servers = $this->servers;
		
		#Split up the URL, get the extension
		if (\Strings::endsWith($route, '/')) {
			$url     = pathinfo($route, PATHINFO_DIRNAME) . '/' . pathinfo($route, PATHINFO_BASENAME);
			$ext     = null;
		} 
		else {
			$url     = pathinfo($route, PATHINFO_DIRNAME) . '/' . pathinfo($route, PATHINFO_FILENAME);
			$ext     = pathinfo($route, PATHINFO_EXTENSION);
		}
		
		#Loop over the servers
		foreach ($servers as $box) { /* @var $box Server */
			if (false !== $t = $box->rewrite($server, $url, $method, $protocol, $ext)) {
				return $t;
			}
		}
		#Implicit else.
		throw new \spitfire\exceptions\PublicException('No such route', 404);
	}
	
	/**
	 * 
	 * @param string $address
	 * @param int    $protocol
	 * @return Server
	 */
	public function server($address = null) {
		if ($address === null) {
			$address = isset($_SERVER['HTTP_HOST'])? $_SERVER['HTTP_HOST'] : 'localhost';
		}
		
		if (isset($this->servers[$address])) { return $this->servers[$address]; }
		return $this->servers[$address] = new Server($address, $this);
	}

	/**
	 * Returns the list of routes. This is usually called by the "Server" to
	 * retrieve generic routes
	 *
	 * @return Server[]
	 */
	public function getServers() {
		return $this->servers;
	}
	
	/**
	 * Allows the router to act with a singleton pattern. This allows your app to
	 * share routes across several points of it.
	 * 
	 * @staticvar Router $instance
	 * @return Router
	 */
	public static function getInstance() {
		static $instance = null;
		if ($instance) { return $instance; }
		else           { return $instance = new Router(); }
	}

}
