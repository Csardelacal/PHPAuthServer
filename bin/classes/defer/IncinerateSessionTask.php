<?php namespace defer;

use spitfire\defer\Task;
use spitfire\defer\Result;
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

class IncinerateSessionTask extends Task
{
	
	private $defer;
	
	public function __construct(TaskFactory $defer)
	{
		$this->defer = $defer;
	}
	
	public function body() : Result 
	{
		$session = db()->table('session')->get('_id', $this->getSettings())->first();
		
		if (!$session) { return new Result('Session was already removed.'); }
		
		/*
		 * If the session was not yet expired when the incinerator started, we will
		 * defer this task for later to ensure that the session is not removed prematurely.
		 */
		if ($session->expires > time()) {
			$this->defer->defer($session->expires, $this);
			return new Result('Session was not yet expired.');
		}
		
		/*
		 * If there's access tokens that would be left dangling, we need to ensure
		 * that they are removed before this session can be garbage collected.
		 */
		$tokens = db()->table('access\token')->get('session', $session)->range(0, 100);
		
		if (!$tokens->isEmpty()) {
			$tokens->each(function ($token) { $this->defer->defer(max($token->expires, time()), new IncinerateAccessTokenTask($token->_id)); });
			$this->defer->defer(time() + 3600, $this);
			return new Result('Session has dependant access tokens.');
		}
		
		/*
		 * If there's access tokens that would be left dangling, we need to ensure
		 * that they are removed before this session can be garbage collected.
		 */
		$refresh = db()->table('access\refresh')->get('session', $session)->where('host', null)->range(0, 100);
		
		if (!$refresh->isEmpty()) {
			$refresh->each(function ($token) { $this->defer->defer(max($token->expires, time()), new IncinerateRefreshTokenTask($token->_id)); });
			$this->defer->defer(time() + 3600, $this);
			return new Result('Session has dependant refresh tokens.');
		}
		
		/*
		 * If the session is already expired and ready to be incinerated, just delete
		 * it.
		 */
		$session->delete();
		return new Result('Incinerated token successfully.');
	}
}
