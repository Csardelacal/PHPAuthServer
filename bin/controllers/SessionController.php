<?php

use spitfire\core\http\URL;
use spitfire\storage\database\pagination\Paginator;

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

class SessionController extends BaseController
{
	
	/**
	 * List the user's sessions.
	 */
	public function index()
	{
		/*
		 * If the user is not properly authenticated, the application cannot continue
		 * and we redirect the user to the sign-in form before and ask it to redirect
		 * them back once they're authenticated.
		 */
		if (!$this->user) {
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url('user', 'login', ['returnto' => strval(URL::current())]));
			return;
		}
		
		$query = db()->table('session')->get('user', $this->user);
		$pages = new Paginator($query);
		
		$this->view->set('sessions', $pages->records());
		$this->view->set('pages', $pages);
	}
	
	/**
	 * Ends a session.
	 * 
	 * @param SessionModel $session
	 */
	public function end(SessionModel $session) 
	{
		
		/*
		 * If the user is not properly authenticated, the application cannot continue
		 * and we redirect the user to the sign-in form before and ask it to redirect
		 * them back once they're authenticated.
		 */
		if (!$this->user) {
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url('user', 'login', ['returnto' => strval(URL::current())]));
			return;
		}
		
		if ($this->user->_id != $session->user->_id) {
			throw new PublicException('You are not authenticated as the user who created this session', 403);
		}
		
		async()->defer(time() + 1200, new defer\IncinerateSessionTask($session->_id));
		async()->defer(time(), new defer\notify\EndSessionTask($session->_id));
		
		$session->expires = time();
		$session->store();
		
		$this->response->setBody('Redirect...')->getHeaders()->redirect(url('session'));
	}
	
	/**
	 * Destroys all the user's sessions and logs them out of all of their devices.
	 * This will affect their current session and log them out of it too.
	 */
	public function nuke()
	{
		
	}
	
}
