<?php namespace spitfire;

use Controller;
use ReflectionClass;
use spitfire\ClassInfo;
use spitfire\core\Context;
use spitfire\core\Environment;
use spitfire\core\Path;
use spitfire\core\router\Parameters;
use spitfire\core\router\reverser\ClosureReverser;
use spitfire\core\router\Router;
use spitfire\exceptions\PrivateException;
use spitfire\exceptions\PublicException;
use spitfire\mvc\View;

/**
 * Spitfire Application Class. This class is the base of every other 'app', an 
 * app is a wrapper of controllers (this allows to plug them into other SF sites)
 * that defines a set of rules to avoid collissions with the rest of the apps.
 * 
 * Every app resides inside of a namespace, this externally defined variable
 * defines what calls Spitfire redirects to the app.
 * 
 * @author César de la Cal<cesar@magic3w.com>
 * @last-revision 2013-10-11
 */
abstract class App
{
	/**
	 * The basedir is the root directory of an application. For spitfire this is 
	 * usually the /bin directory. This directory contains all the app specific
	 * data. Including controllers, views and models.
	 * 
	 * In the specific case of Spitfire this folder also contains the 'child apps'
	 * that can be added to it.
	 *
	 * @var string
	 */
	private $basedir;
	private $URISpace;
	
	/**
	 * Creates a new App. Receives the directory where this app resides in
	 * and the URI namespace it uses.
	 * 
	 * @param string $basedir The root directory of this app
	 * @param string $URISpace The URI namespace it 'owns'
	 */
	public function __construct($basedir, $URISpace) {
		$this->basedir  = $basedir;
		$this->URISpace = $URISpace;
	}
	
	public function getBaseDir() {
		return $this->basedir;
	}
	
	public function getURISpace() {
		return $this->URISpace;
	}
	
	/**
	 * 
	 * @deprecated since version 0.1-dev 20171129
	 * @param type $contenttype
	 * @return type
	 */
	public function getDirectory($contenttype) {
		switch($contenttype) {
			case ClassInfo::TYPE_CONTROLLER:
				return $this->getBaseDir() . 'controllers/';
			case ClassInfo::TYPE_MODEL:
				return $this->getBaseDir() . 'models/';
			case ClassInfo::TYPE_VIEW:
				return $this->getBaseDir() . 'views/';
			case ClassInfo::TYPE_LOCALE:
				return $this->getBaseDir() . 'locales/';
			case ClassInfo::TYPE_BEAN:
				return $this->getBaseDir() . 'beans/';
			case ClassInfo::TYPE_COMPONENT:
				return $this->getBaseDir() . 'components/';
			case ClassInfo::TYPE_STDCLASS:
				return $this->getBaseDir() . 'classes/';
			case ClassInfo::TYPE_APP:
				return $this->getBaseDir() . 'apps/';
		}
	}

	/**
	 * Checks if the current application has a controller with the name specified
	 * by the single argument this receives. In case a controller is found and
	 * it is not abstract the app will return the fully qualified class name of 
	 * the Controller.
	 *
	 * It should not be necessary to check the return value with the === operator
	 * as the return value on success should never be matched otherwise.
	 *
	 * @param  string $name The name of the controller being searched
	 * @return string|boolean The name of the class that has the controller
	 */
	public function hasController($name) {
		$name = (array)$name;
		$c    = $this->getNameSpace() . implode('\\', $name) . 'Controller';
		if (!class_exists($c)) { return false; }

		$reflection = new ReflectionClass($c);
		if ($reflection->isAbstract()) { return false; }
			
		return $c;
	}
	
	/**
	 * Creates a new Controller inside the context of the request. Please note 
	 * that this may throw an Exception due to the controller not being found.
	 * 
	 * @param string $controller
	 * @param Context $intent
	 * @return Controller
	 * @throws PublicException
	 */
	public function getController($controller, Context$intent) {
		#Get the controllers class name. If it doesn't exist it'll be false
		$c = $this->hasController($controller);
		
		#If no controller was found, we can throw an exception letting the user know
		if ($c === false) { throw new PublicException("Page not found", 404, new PrivateException("Controller {$controller[0]} not found", 0) ); }
		
		#Otherwise we will instantiate the class and return it
		return new $c($intent);
	}
	
	public function getControllerURI($controller) {
		return explode('\\', substr(get_class($controller), strlen($this->getNameSpace()), 0-strlen('Controller')));
	}
	
	public function getView(Controller$controller) {
		
		$name = implode('\\', $this->getControllerURI($controller));
		
		$c = $this->getNameSpace() . $name . 'View';
		if (!class_exists($c)) { $c = View::class; }
		
		return new $c($controller->context);
	}
	
	/**
	 * 
	 * @deprecated since version 0.1-dev 20180524
	 * @return type
	 */
	public function getControllerDirectory() {
		return $this->getBaseDir() . 'controllers/';
	}
	
	/**
	 * Creates the default routes for this application. Spitfire will assume that
	 * a /app/controller/action/object type of path is what you wish to use for
	 * your app. If you'd rather have custom rules - feel free to override these.
	 */
	public function createRoutes() {
		$ns       = $this->URISpace? '/' . $this->URISpace : '';
		$uriSpace = $this->URISpace;
		
		#The default route just returns a path based on app/controller/action/object
		#If your application does not wish this to happen, please override createRoutes
		#with your custome code.
		$default = Router::getInstance()->request($ns, function (Parameters$params, Parameters$server, $extension) use ($uriSpace) {
			$args = $params->getUnparsed();
			return new Path($uriSpace, array_shift($args), array_shift($args), $args, $extension);
		});
		
		#The reverser for the default route is rather simple again. 
		#It will concatenate app, controller and action
		$default->setReverser(new ClosureReverser(function (Path$path, $explicit = false) {
			$app        = $path->getApp();
			$controller = $path->getController();
			$action     = $path->getAction();
			$object     = $path->getObject();
			
			if ($controller === (array)Environment::get('default_controller') && empty($object) && !$explicit) { $controller = Array(); }
			if ($action     ===        Environment::get('default_action')     && empty($object) && !$explicit) { $action     = ''; }
			
			return '/' . trim(implode('/', array_filter(array_merge([$app], (array)$controller, [$action], $object))), '/');
		}));
	}
	
	abstract public function enable();
	abstract public function getNameSpace();
	abstract public function getAssetsDirectory();
	

	/**
	 * Returns the directory the templates are located in. This function should be 
	 * avoided in favor of the getDirectory function.
	 * 
	 * @deprecated since version 0.1-dev 20150423
	 */
	public function getTemplateDirectory() {
		return $this->getBaseDir() . 'templates/';
	}
	
}
