<?php namespace spitfire\core;

use Controller;
use publicException;
use spitfire\App;
use spitfire\cache\MemcachedAdapter;
use spitfire\core\annotations\ActionReflector;
use spitfire\core\annotations\AnnotationParser;
use spitfire\core\Request;
use spitfire\core\Response;
use spitfire\exceptions\PrivateException;
use spitfire\InputSanitizer;
use spitfire\io\session\Session;
use spitfire\mvc\middleware\MiddlewareStack;
use spitfire\mvc\View;
use function spitfire;

/**
 * The context is a wrapper for an Intent. Basically it describes a full request
 * for a page inside Spitfire. Usually you would have a single Context in any 
 * execution.
 * 
 * Several contexts will usually only be found in Unit Tests that mock the context,
 * when using nested controllers or in a CLI application.
 * 
 * @link http://www.spitfirephp.com/wiki/index.php/Class:Context
 * @author César de la Cal <cesar@magic3w.com>
 * @last-revision 2013-10-25
 */
class Context implements ContextInterface
{
	/**
	 * This is a reference to the context itself. This is a little helper for the
	 * View and Controller objects that do expose the Context's elements via Magic
	 * methods, this way we do not need any extra cases for the context.
	 *
	 * @var Context
	 */
	public $context;
	
	/**
	 *
	 * @var MiddlewareStack
	 */
	public $middleware;
	
	/**
	 * The application running the current context. The app will provide the controller
	 * to handle the request / context provided.
	 *
	 * @var App
	 */
	public $app;
	
	/**
	 * The controller is in charge of preparig a proper response to the request.
	 * This is the first logical level that is user-defined.
	 * 
	 * @var Controller
	 */
	public $controller;
	public $action;
	public $object;
	public $extension;
	public $annotations;
	
	public $validation;
	
	/**
	 * Holds the view the app uses to handle the current request. This view is in 
	 * charge of rendering the page once the controller has finished processing
	 * it.
	 * 
	 * @var View
	 */
	public $view;
	
	public $parameters;
	public $get;
	public $post;
	public $cache;
	/**
	 *
	 * @var Request 
	 */
	public $request;
	public $response;
	public $session;
	
	function __construct() {
		$this->context = $this;
	}
	
	public static function create() {
		$context = new Context;
		$context->get        = $_GET;
		$context->post       = new InputSanitizer($_POST);
		$context->session    = Session::getInstance();
		$context->cache      = MemcachedAdapter::getInstance();
		$context->request    = Request::get();
		$context->parameters = $context->request->getPath()->getParameters();
		$context->response   = new Response($context);
		$context->middleware = new MiddlewareStack($context);
		
		$context->app        = spitfire()->getApp($context->request->getPath()->getApp());
		$context->controller = $context->app->getController($context->request->getPath()->getController(), $context);
		$context->action     = $context->request->getPath()->getAction();
		$context->object     = $context->request->getPath()->getObject();
		
		$context->view        = $context->app->getView($context->controller);
		
		try {
			$reflector            = new \ReflectionMethod($context->controller, $context->action);
			$annotationParser     = new AnnotationParser();
			$context->annotations = $annotationParser->parse($reflector->getDocComment());
		} catch(\Exception$e) {
			$context->annotations = [];
		}
		
		return $context;
	}
	
	public function run() {
		#Run the onload
		if (method_exists($this->controller, '_onload') ) {
			call_user_func_array(Array($this->controller, '_onload'), Array($this->action));
		}
		
		$this->middleware->before();
		
		#Check if the controller can handle the request
		$request = Array($this->controller, $this->action);
		if (is_callable($request)) { $_return = call_user_func_array($request, $this->object); }
		else { throw new publicException('Page not found', 404, new PrivateException('Action not found', 0)); }
		
		$this->middleware->after();
		
		if ($_return instanceof Context) { return $_return; }
		else                             { return $this; }
	}
	
	public function __clone() {
		$this->context = $this;
	}
}
