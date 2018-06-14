<?php

use connection\ContextModel;
use signature\Signature;
use spitfire\exceptions\PublicException;
use spitfire\storage\database\pagination\Paginator;

/* 
 * The MIT License
 *
 * Copyright 2017 César de la Cal Bretschneider <cesar@magic3w.com>.
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

class ContextController extends BaseController
{
	
	public function index(AuthAppModel$app = null) {
		if ($app === null) {
			$app = $this->authapp;
		}
		
		if ($this->authapp->_id !== $app->_id || !$this->isAdmin) {
			throw new PublicException('No permissions to access this page', 403);
		}
		
		if ($app === null) {
			throw new PublicException('No application found', 404);
		}
		
		$query = db()->table('connection\context')->get('app', $app)->where('expires', '>', time());
		$pagination = new Paginator($query);
		
		$this->view->set('records', $pagination->records());
		$this->view->set('pag', $pagination);
	}
	
	public function create() {
		/*
		 * Get the context that the application is pretending to create.
		 */
		$context   = isset($_GET['context'])? $_GET['context'] : null;
		
		if (!$this->authapp && !$this->isAdmin) {
			throw new PublicException('Application or administrative authentication required for this endpoint', 401);
		}
		
		/*@var $record ContextModel*/
		$record = db()->table('connection\context')->newRecord();
		$record->ctx     = $context;
		$record->app     = $this->authapp;
		$record->title   = _def($_POST['name'], 'Unnamed context');
		$record->descr   = _def($_POST['description'], 'Missing description');
		$record->expires = _def($_POST['expires'], 86400 * 90) + time();
		$record->store();
		
		$this->view->set('result', $record);
	}
	
	public function edit($appid, $ctx) {
		
	}
	
	public function delete($appid, $ctx) {
		
	}
	
	public function deny(connection\AuthModel$auth) {
		
		if ($this->token || $this->authapp) {
			throw new PublicException('Insufficient context', 403);
		}
		
		if ($auth->user === null) {
			//Do nothing, the previous context will be overriden
		}
		elseif ($auth->user->_id === $this->user->_id) {
			$auth->expires = time();
			$auth->store();
		}
		else {
			throw new PublicException('No permissions to edit this context', 403);
		}
		
		$record = db()->table('connection\auth')->newRecord();
		$record->source = $auth->source;
		$record->target = $auth->target;
		$record->user   = $this->user;
		$record->state  = connection\AuthModel::STATE_DENIED;
		$record->context= $auth->context;
		$record->expires= null;
		$record->final  = false;
		$record->store();
		
		$this->response->setBody('Redirecting...')->getHeaders()->redirect(url('permissions', 'for', $auth->target->_id));
	}
	
	public function revoke(connection\AuthModel$auth) {
		
		if ($this->token || $this->authapp) {
			throw new PublicException('Insufficient context', 403);
		}
		
		if ($auth->user->_id === $this->user->_id) {
			$auth->expires = time();
			$auth->store();
		}
		else {
			throw new PublicException('No permissions to edit this context', 403);
		}
		
		$this->response->setBody('Redirecting...')->getHeaders()->redirect(url('permissions', 'for', $auth->target->_id));
	}
	
}