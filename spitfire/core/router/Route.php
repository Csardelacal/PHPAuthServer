<?php namespace spitfire\core\router;

use Closure;
use Exception;
use spitfire\core\router\reverser\ClosureReverser;
use spitfire\core\router\reverser\RouteReverserInterface;

/**
 * A route is a class that rewrites a URL path (route) that matches a
 * route or pattern (old_route) into a new route that the system can 
 * use (new_route) to handle the current request.
 * 
 * A Route will only accept Closures, Responses or Paths (including arrays that
 * can be interpreted as Paths by the translation class) as the target.
 * 
 * @author César de la Cal <cesar@magic3w.com>
 */
class Route extends RewriteRule
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
	
	private $reverser = null;
	
	/**
	 * 
	 * @param string $URI
	 * @param string $method
	 * @param string $protocol
	 * @param Parameters $server
	 * @param string $extension
	 * @return \spitfire\core\Path|\spitfire\core\Response
	 */
	public function rewrite($URI, $method, $protocol, $server, $extension = 'php') {
		$params = $this->getSource()->test($URI);
		
		/*
		 * Closures are the most flexible way to handle requests. They allow to 
		 * determine how the application should react depending on any of the
		 * request's components.
		 */
		if ($this->getTarget() instanceof Closure) {
			return call_user_func_array($this->getTarget(), Array($params, $server, $extension, $method, $protocol));
		}
		
		/*
		 * When using a parameterized path, the idea is to replace the parameters
		 * we extracted from the URL and construct a valid Path that can then be
		 * used to answer the request.
		 */
		if ($this->getTarget() instanceof ParametrizedPath) {
			return $this->getTarget()->replace($server->merge($params)->setUnparsed($params->getUnparsed()))->setFormat($extension);
		}
		
	}
	
	/**
	 * 
	 * @return RouteReverserInterface
	 */
	public function getReverser() {
		if ($this->reverser || !$this->getTarget() instanceof ParametrizedPath) { return $this->reverser; }

		return $this->reverser = new ClosureReverser(function ($path) {
			try { return $this->getSource()->reverse($this->getTarget()->extract($path)); } 
			catch (Exception$e) { return false; }
		});

	}
	
	/**
	 * 
	 * @param RouteReverserInterface $reverser
	 * @return Route
	 */
	public function setReverser($reverser) {
		$this->reverser = $reverser;
		return $this;
	}
}
