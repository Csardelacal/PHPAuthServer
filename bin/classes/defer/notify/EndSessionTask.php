<?php namespace defer\notify;

use spitfire\defer\Result;
use spitfire\defer\Task;

/* 
 * The MIT License
 *
 * Copyright 2021 César de la Cal Bretschneider <cesar@magic3w.com>.
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
 * This task gets executed whenever the application wishes to let the clients
 * know that a certain session was ended.
 * 
 * @todo Once orbital station is running, us it's API to perform these tasks
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class EndSessionTask implements Task
{
	
	public function body($settings): Result 
	{
		/*
		 * Locate the session first.
		 */
		$session = db()->table('session')->get('_id', $settings)->first();
		if (!$session) { return new Result('The session was already incinerated'); }
		
		return new Result('Webhooks are currently disabled');
		
		/*
		 * Create a hook client that can send the notification out.
		 */
		#TODO: The hook needs to be found via IOC
		$hook = null;
		$count = 0;
		
		/*
		 * We need to find all the tokens associated with this session, so we can
		 * notify the applications that (if they're being used to hold a session)
		 * they should be expired.
		 */
		$tokens = db()->table('access\token')->get('session', $session)->all();
		
		foreach ($tokens as $token) {
			/*@var $token \access\TokenModel*/
			$payload = ['token' => $token->token, 'session' => $session->_id];
			$hook->trigger(sprintf('app%s.session.logout', $token->client->appID), $payload);
			
			$count++;
		}
		
		/*
		 * Applications that are holding refresh tokens to renew the session should
		 * also be notified.
		 */
		$refresh = db()->table('access\token')->get('session', $session)->all();
		
		foreach ($refresh as $token) {
			/*@var $token \access\TokenModel*/
			$payload = ['token' => $token->token, 'session' => $session->_id];
			$hook->trigger(sprintf('app%s.session.logout', $token->client->appID), $payload);
			
			$count++;
		}
		
		return new Result(sprintf('Successfully notified %d applications', $count));
	}

}
