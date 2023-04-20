<?php namespace spitfire\core\http;

use spitfire\core\Environment;
use spitfire\core\router\Router;
use spitfire\core\router\Server;

class AbsoluteURL extends URL
{
	
	const PROTO_HTTP  = 'http';
	const PROTO_HTTPS = 'https';
	
	private $domain;
	
	private $proto    = self::PROTO_HTTP;
	
	/**
	 * The reverser property acts as a cache, removing the need to cycle through
	 * the different reversers and their rules to check if they're a fit 
	 * candidate.
	 *
	 * @var \spitfire\core\router\reverser\ServerReverserInterface
	 */
	private $reverser = null;
	
	
	/**
	 * Set the domain name this URL points to. This is intended to address
	 * Spitfire apps that work on a multi-domain environment / subdomains
	 * and require linking to itself on another domain. They are also good 
	 * for sharing / email links where the URL without server name would
	 * be useless.
	 * 
	 * Since April 2017, you can provide this method with an array of parameters
	 * that the router parses when handling a request. This allows your application
	 * to not only manage custom server names but also to write URLs pointing
	 * there depending on your settings.
	 * 
	 * @param string $domain The domain of the URL. I.e. www.google.com
	 * @return absoluteURL
	 */
	public function setDomain($domain) {
		$this->domain   = $domain;
		$this->reverser = $this->getReverser();
		return $this;
	}
	
	public function getDomain() {
		return $this->domain;
	}
	
	public function setProtocol($proto) {
		$this->proto = $proto;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getServerName() {
		
		/*
		 * The user provided parameters as the domain, therefore he expects Spitfire
		 * to look up a valid server for the route.
		 */
		if (is_array($this->domain)) {
			
			/*
			 * Given an array as path and no reverser means that the user provided 
			 * parameters for an impossible route and therefore the application 
			 * cannot properly continue.
			 */
			if (!$this->reverser) {
				throw new \spitfire\exceptions\PrivateException('No server found for given params', 1706212055);
			}
			
			return $this->reverser->reverse($this->domain);
		}
		
		if (is_string($this->domain)) {
			return $this->domain;
		}
		
		#Default
		return Environment::get('server_name')? Environment::get('server_name') : $_SERVER['SERVER_NAME']; 
	}
	
	public static function current() {
		$ctx = current_context();
		
		if (!$ctx) { 
			throw new PrivateException("No context for URL generation"); 
		}
		
		return new self($ctx->app, $ctx->app->getControllerURI($ctx->controller), $ctx->action, $ctx->object, $ctx->extension, $_GET);
	}
	
	public static function asset($asset_name, $app = null) {
		$path = parent::asset($asset_name, $app);
		
		$proto  = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'? self::PROTO_HTTPS : self::PROTO_HTTP;
		$domain = Environment::get('server_name')? Environment::get('server_name') : $_SERVER['SERVER_NAME'];
		
		return $proto . '://' . $domain . $path;
	}
	
	public static function canonical() {
		
		#Get the relative canonical URI
		$canonical = URL::canonical();
		
		#Prepend protocol and server and return it
		return $canonical->absolute();
	}
	
	public function getRoutes() {
		/*
		 * If the developer provided a set of parameters to reverse the route we
		 * use those.
		 */
		if ($this->reverser) { 
			return $this->getReverser()->getServer()->getRoutes()->toArray();
		}
		
		/*
		 * Otherwise, if the dev provided a server which potentially has no routes,
		 * then we return either the server's routes or the default ones.
		 */
		elseif($this->domain) {
			$router = Router::getInstance();
			return $router->server()->getRoutes()->toArray()? : $router->getRoutes()->toArray();
		}
		
		/*
		 * Otherwise we use the globals.
		 */
		else {
			return parent::getRoutes();
		}
	}
	
	public function getReverser() {
		#If the user didn't pass parameters, this operation is worthless.
		if (!is_array($this->domain)) { return null; }
		
		#Get the servers we registered for the router
		$router  = Router::getInstance();
		$servers = $router->getServers();
		
		foreach ($servers as $s) {
			/*@var $s Server*/
			/*@var $r BaseServerReverser*/
			$r = $s->getReverser();
			
			if ($r->reverse($this->domain)) {
				return $this->reverser = $r;
			}
		}
		
		return $this->reverser = null;
	}

	public function __toString() {
		$rel    = parent::__toString();
		$proto  = $this->proto;
		$domain = $this->getServerName();
		
		return $proto . '://' . $domain . $rel;
	}
}
