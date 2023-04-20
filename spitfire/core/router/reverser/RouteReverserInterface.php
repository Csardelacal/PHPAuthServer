<?php namespace spitfire\core\router\reverser;

use spitfire\core\Path;

/**
 * A route reverser allows any route to provide a mechanism to reverse any given
 * route. This allows the application to build URLs that match the route they'll
 * be guided through.
 */
interface RouteReverserInterface
{
	
	/**
	 * The reverse method allows a route to provide a method to construct a path 
	 * that will then be used by the URL string builder.
	 * 
	 * @param Path    $path     The path to be reversed.
	 * @param boolean $explicit If false, the URL should be as brief and human readable as possible
	 * 
	 * @return string|boolean A string containing the desired path / bool(false)
	 *         to indicate that the route does not accept this method.
	 */
	function reverse(Path$path, $explicit = false);
}
