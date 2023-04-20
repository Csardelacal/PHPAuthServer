<?php namespace spitfire\core\router\reverser;

/**
 * This interface allows a developer to create server reversers for servers. 
 * Usually the servers are less complicated than routes and therefore won't 
 * require any dedicated reverser, but any class implementing this can suit
 * that role.
 */
interface ServerReverserInterface
{
	
	/**
	 * Reverses the Server hostname from a set of parameters, allowing to reassemble
	 * a full absolute URL from the given parameters.
	 * 
	 * @param string[] $parameters
	 * @return bool|string The rewriten server URI or false if there isn't
	 */
	function reverse($parameters);

	/** 
	 * Gets the Server for the reverser. This allows the app to extract the server's
	 * routes to merge them with the global routes.
	 * 
	 * @return \spitfire\core\router\Server 
	 */
	function getServer();
}
