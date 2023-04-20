<?php namespace spitfire\core;

use ReflectionMethod;
use spitfire\App;
use spitfire\cache\MemcachedAdapter;
use spitfire\core\annotations\AnnotationParser;
use spitfire\exceptions\PublicException;
use spitfire\mvc\middleware\MiddlewareStack;
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
 * @last-revision 2018-05-28
 */
class ContextCLI implements ContextInterface
{
	/**
	 * This is a reference to the context itself. This is a little helper for the
	 * View and Controller objects that do expose the Context's elements via Magic
	 * methods, this way we do not need any extra cases for the context.
	 *
	 * @var Context
	 */
	public $context;
	
	public $middleware;
	
	/**
	 * The application running the current context. The app will provide the controller
	 * to handle the request / context provided.
	 *
	 * @var App
	 */
	public $app;
	
	
	public $director;
	public $action;
	public $object;
	public $extension;
	public $annotations;
	
	public $parameters;
	public $cache;
	
	function __construct() {
		$this->context = $this;
	}
	
	public static function create($director, $action, \spitfire\io\cli\arguments\CLIArguments$args) {
		$context = new ContextCLI;
		$context->cache      = MemcachedAdapter::getInstance();
		$context->parameters = null;
		$context->middleware = new MiddlewareStack($context);
		
		if (!class_exists($director)) {
			throw new PublicException('Invalid director');
		}
		
		$name     = $director;
		$instance = new $name($context);
		
		$context->app        = spitfire()->findAppForClass($director);
		$context->director   = $instance;
		$context->action     = $action;
		$context->object     = $args->arguments()->toArray();
		$context->parameters = $args->parameters();
		
		/*
		 * Parse the annotations.
		 */
		$reflector            = new ReflectionMethod($context->director, $context->action);
		$annotationParser     = new AnnotationParser();
		$context->annotations = $annotationParser->parse($reflector->getDocComment());
		
		return $context;
	}
	
	public function run() {
		
		if(is_callable([$this->director, $this->action])) {
			/*
			 * Execute the ingoing middleware first
			 */
			$this->middleware->before($this);
			
			$_ret = call_user_func_array([$this->director, $this->action], $this->object);
			
			$this->middleware->after($this);
			
			return $_ret;
		}
		else {
			throw new PublicException('Invalid action');
		}
		
	}
	
	public function __clone() {
		$this->context = $this;
	}
}
