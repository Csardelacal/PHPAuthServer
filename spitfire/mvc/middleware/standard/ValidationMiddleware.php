<?php namespace spitfire\mvc\middleware\standard;

use spitfire\collection\Collection;
use spitfire\core\ContextCLI;
use spitfire\core\ContextInterface;
use spitfire\core\Response;
use spitfire\exceptions\PublicException;
use spitfire\mvc\middleware\MiddlewareInterface;
use spitfire\validation\parser\Parser;

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

class ValidationMiddleware implements MiddlewareInterface
{
	
	public function before(ContextInterface $context) {
		$expressions = $context->annotations['validate']?? null;
		$parser      = new Parser();
		
		$context->validation = new Collection();
		
		if (!$expressions) {
			return;
		}
		
		foreach ($expressions as $expression) {
			$throw = true;
			
			if(substr($expression, 0, 2) === '>>') {
				$expression = substr($expression, 2);
				$throw      = false;
			}
			
			$data = [
				'GET'  => $_GET->getRaw(), 
				'POST' => $_POST, 
				'OBJ'  => $context instanceof ContextCLI? $context->parameters : $context->object
			];
			
			$validator = $parser->parse($expression)->setValue($data);
			
			if (!$validator->isOk()) {
				if ($throw) { throw new PublicException('Validation failed', 400); }
				$context->validation->add(collect($validator->getMessages()));
			}
		}
	}
	
	public function after(ContextInterface $context, Response $response = null) {
		
	}

}
