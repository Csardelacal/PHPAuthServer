<?php namespace defer\tasks;

use spitfire\defer\Task;
use spitfire\defer\TaskFactory;

/*
 * The MIT License
 *
 * Copyright 2020 César de la Cal Bretschneider <cesar@magic3w.com>.
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

class EndSessionTask implements Task
{
	
	private $defer;
	
	public function __construct(TaskFactory $defer)
	{
		$this->defer = $defer;
	}
	
	public function body($settings) : void
	{
		$session = db()->table('session')->get('_id', $settings)->first();
		
		/**
		 * No session exists anymore
		 */
		if (!$session) {
			return;
		}
		
		/**
		 * @todo Mark the session status flag as terminating.
		 */
		
		/*
		 * If the session was not yet expired when the incinerator started, we will
		 * defer this task for later to ensure that the session is not removed prematurely.
		 */
		if ($session->expires > time()) {
			$this->defer->defer($session->expires, __CLASS__, $settings);
			return; #Session was not yet expired
		}
		
		/**
		 * When the session is ended, the tokens associated with it need to be ended too.
		 */
		$tokens = db()->table('access\token')->get('session', $session)->where('expires', '>', time())->all();
		var_dump($tokens->count());
		
		/**
		 * If the tokens are all expired the session is properly finished and can be
		 * marked as properly finished.
		 *
		 * @todo Mark the session status as terminated
		 */
		if ($tokens->isEmpty()) {
			return;
		}
		
		/**
		 * All the access tokens that this session issued must be appropriately expired and
		 * removed. Applications should no longer be using them.
		 */
		$tokens->each(function ($token) {
			$token->expires = time();
			$token->store();
			
			/**
			 * Defer the tasks of ending and incinerating the token.
			 */
			$this->defer->defer(1, EndAccessTokenTask::class, $token->_id);
			$this->defer->defer(1800, IncinerateAccessTokenTask::class, $token->_id);
		});
		
		/**
		 * Requeue the session ending task.
		 */
		$this->defer->defer(1, EndSessionTask::class, $settings);
		
		return; # Incinerated token successfully
	}
}
