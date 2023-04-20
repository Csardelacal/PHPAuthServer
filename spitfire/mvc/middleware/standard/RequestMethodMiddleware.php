<?php namespace spitfire\mvc\middleware\standard;

use spitfire\core\ContextInterface;
use spitfire\core\Response;
use spitfire\exceptions\PublicException;
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
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class RequestMethodMiddleware implements MiddlewareInterface
{
	
	public function after(ContextInterface $context, Response $response = null) {
		
	}
	
	/**
	 * Checks whether the request being sent by the user is acceptable for the 
	 * selected action. This allows your application to set a bunch of valid methods
	 * that will avoid this one throwing an exception informing about the invalid
	 * request.
	 * 
	 * @return mixed
	 * @throws PublicException If the user is throwing a request with one method
	 *			that is not accepted.
	 */
	public function before(ContextInterface $context) {
		
		if (empty($context->annotations['request-method'])) {
			return;
		}
		
		$annotation = reset($context->annotations['request-method']);
		$accepted   = explode(' ', $annotation);
		
		foreach($accepted as $ok) {
			if (strtolower($ok) === strtolower($_SERVER['REQUEST_METHOD'])) {
				return;
			}
		}
		
		throw new PublicException("No valid request", 400);
	}

}
