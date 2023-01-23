<?php namespace defer\tasks;

use spitfire\defer\Task;
use spitfire\defer\TaskFactory;

/*
 * The MIT License
 *
 * Copyright 2023 César de la Cal Bretschneider <cesar@magic3w.com>.
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

class IncinerateAccessCodeTask implements Task
{
	
	private $defer;
	
	public function __construct(TaskFactory $defer)
	{
		$this->defer = $defer;
	}
	
	public function body($settings) : void
	{
		$token = db()->table('access\code')->get('_id', $settings)->first();
		
		/**
		 * No token exists anymore
		 */
		if (!$token) {
			return;
		}
		
		if ($token->expires > time()) {
			return;
		}
		
		/*
		 * If the token is already expired and ready to be incinerated, just delete
		 * it.
		 */
		$token->delete();
		
		return; # Incinerated token successfully
	}
}
