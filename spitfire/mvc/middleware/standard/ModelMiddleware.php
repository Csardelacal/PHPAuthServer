<?php namespace spitfire\mvc\middleware\standard;

use ReflectionClass;
use spitfire\core\Context;
use spitfire\core\ContextInterface;
use spitfire\core\Response;
use spitfire\Model;
use spitfire\mvc\middleware\MiddlewareInterface;

/* 
 * The MIT License
 *
 * Copyright 2018 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * This middleware component does fulfill the rather simple task of handling
 * Arguments to Controller actions which require passing a Model as argument.
 * 
 * This makes it less tedious to write code that allows developers to manipulate
 * a single record from a database. Your code may look like this:
 * 
 * <code>public function detail(Usermodel$user)</code>
 * 
 * In this case, if the user does not provide a valid user-id as parameter, the 
 * application will fail with a server error.
 * 
 * If you wish to make the parameter optional, just write your code like this:
 * 
 * <code>public function detail(UserModel$user = null)</code>
 * 
 * If the user didn't provide the value, it will be null. You then will have to
 * test the value before using it.
 */
class ModelMiddleware implements MiddlewareInterface
{
	
	private $db;
	
	public function __construct($db) {
		$this->db = $db;
	}
	
	/**
	 * 
	 * @param Context $context
	 */
	public function before(ContextInterface $context) {
		
		if (!method_exists($context instanceof Context? $context->controller : $context->director, $context->action)) { return; }
		
		$controller = new ReflectionClass($context instanceof Context? $context->controller : $context->director);
		$action     = $controller->getMethod($context->action);
		$object     = $context->object;
		
		$params     = $action->getParameters();
		
		for ($i = 0; $i < count($params); $i++) {
			/*@var $param \ParameterReflection*/
			$param = $params[$i];
			
			if (!$param->getType()) { continue; }
			if (!$param->getType()->getName()) { continue; }
			if ($param->getType()->isBuiltin()) { continue; }
			if (!(new ReflectionClass($param->getType()->getName()))->isSubclassOf(Model::class)) { continue; }
			
			$table = $this->db->table(substr($param->getType()->getName(), 0, 0 - strlen('model')));
			$object[$i] = $table->getById($object[$i]);
			
		}
		
		$context->object = $object;
	}
	
	public function after(ContextInterface $context, Response $response = null) {
		
	}

}
