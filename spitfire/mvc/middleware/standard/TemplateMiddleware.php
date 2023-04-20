<?php namespace spitfire\mvc\middleware\standard;

use spitfire\core\ContextInterface;
use spitfire\core\Response;
use spitfire\mvc\middleware\MiddlewareInterface;
use function current_context;

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

class TemplateMiddleware implements MiddlewareInterface
{
	
	public function after(ContextInterface $context, Response $response = null) {
		
	}
	
	/**
	 * Defines whether the current template is rendered or not and what file is
	 * used for that purpose. This allows your application to quickly define
	 * templates that are not located in normal locations.
	 * 
	 * @return mixed
	 */
	public function before(ContextInterface $context) {
		
		if (!isset($context->annotations['template'])) {
			return;
		}
		
		$file = reset($context->annotations['template']);
		
		if ($file == 'none') {
			return $context->view->setRenderTemplate(false);
		}
		
		$context->view->setFile($file);
	}

}
