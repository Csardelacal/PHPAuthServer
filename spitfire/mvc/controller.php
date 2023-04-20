<?php

use spitfire\core\Context;
use spitfire\exceptions\PrivateException;
use spitfire\exceptions\PublicException;
use spitfire\mvc\MVC;

abstract class Controller extends MVC
{
	
	
	public function __construct(Context$intent) {
		parent::__construct($intent);
	}
	
	/**
	 * The __call method of controllers is responsible for finding 'nested controllers'
	 * capable of handling a request. If such a controller is found Spitfire
	 * will modify the current context and handle that separately.
	 * 
	 * @link http://www.spitfirephp.com/wiki/index.php/Nested_controllers For informtion about nested controllers
	 * 
	 * @param string $name
	 * @param mixed $arguments
	 * @return Context The context that has finally solved the request.
	 * @throws publicException If there is no inhertiable controller found
	 */
	public function __call($name, $arguments) {
		$controller = $this->app->getControllerURI($this);
		$action = $name;
		$object = $arguments;
		
		if (class_exists(strtolower(implode('\\', $controller)) . '\\' . ucfirst($action) . 'Controller')) {
			
			array_push($controller, $action);
			
			$action     = array_shift($object);
			$request    = spitfire\core\Request::get();
			$path       = $request->getPath();
			
			$path->setController($controller);
			$path->setAction($action);
			$path->setObject($object);
			
			//TODO: This is a temporaty fix, the code should not be loading the middleware twice.
			$ctx = current_context(Context::create());
			include CONFIG_DIRECTORY . 'middleware.php';
			return $ctx->run();
		}
		else {
			throw new PublicException("Page not found", 404, new PrivateException('Action not found', 0));
		}
	}
	
}
