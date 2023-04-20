<?php namespace spitfire\mvc\middleware;

use spitfire\core\Context;

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

class MiddlewareStack
{
	
	/**
	 *
	 * @var Context
	 */
	private $ctx;
	
	/**
	 *
	 * @var MiddlewareInterface[]
	 */
	private $middleware = [];
	
	public function __construct(\spitfire\core\ContextInterface$ctx) {
		$this->ctx = $ctx;
	}
	
	public function register(MiddlewareInterface$mw) {
		$this->middleware[] = $mw;
	}
	
	public function before() {
		foreach ($this->middleware as $middleware) {
			$middleware->before($this->ctx);
		}
	}
	
	public function after() {
		foreach ($this->middleware as $middleware) {
			$middleware->after($this->ctx, $this->ctx instanceof Context? $this->ctx->response : null);
		}
	}
	
}
