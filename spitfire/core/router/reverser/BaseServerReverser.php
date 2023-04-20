<?php namespace spitfire\core\router\reverser;

use spitfire\core\router\Pattern;
use spitfire\core\router\Server;

/**
 * This class allows to reverse server in the router. Usually a server can be used 
 * to extract some additional data from a route. These reverser can be used to 
 * generate a hostname from that additional data.
 * 
 * Usually, this data will come provided by the domain data you pass to the 
 * absolute URL class.
 */
class BaseServerReverser implements ServerReverserInterface
{
	/** 
	 * The patterns parsing the server's parameters, we can use these to rebuild
	 * the original pattern.
	 * 
	 * @var Pattern[] 
	 */
	private $pattern;
	
	/** 
	 * The server that this reverses for. We need this variable for a rather simple
	 * reason, once the system determined that it can indeed use this server to 
	 * reverse a route it will need the server to fetch the appropriate routes
	 * for the server.
	 * 
	 * @var Server 
	 */
	private $server;

	/**
	 * Creates a new default server reverser. This allows the application to use
	 * the default pattern matching system to detect whether certain parameters
	 * match a certain server.
	 * 
	 * @param Pattern[] $pattern
	 * @param Server    $server
	 */
	public function __construct($pattern, $server) {
		$this->pattern = $pattern;
		$this->server  = $server;
	}

	/** 
	 * {@inheritdoc}
	 * 
	 * Servers are relatively easy to parse, they allow to set parameters via 
	 * certain pieces of the URL. This allows, for example, to set localization 
	 * based on a subdomain of the app.
	 */
	public function reverse($parameters) {
		$result = Array();
		
		foreach ($this->pattern as $p) {
			/*@var $p Pattern*/
			if     (!$p->getName())                    { $result[] = $p->getPattern()[0]; }
			elseif (isset($parameters[$p->getName()])) { $result[] = $parameters[$p->getName()]; }
			else                                       { return false; }
		}
		
		return implode('.', $result);
	}

	/** 
	 * @inheritdoc 
	 */
	public function getServer() {
		return $this->server;
	}

}
