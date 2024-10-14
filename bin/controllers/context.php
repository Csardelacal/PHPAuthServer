<?php

use connection\AuthModel;
use connection\ContextModel;
use spitfire\exceptions\PublicException;
use spitfire\storage\database\pagination\Paginator;
use spitfire\exceptions\HTTPMethodException;
use spitfire\validation\ValidationException;

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
	
	/**
	 * 
	 * @param AuthAppModel $app
	 * @throws PublicException
	 */
	public function index(AuthAppModel$app = null) {
		if ($app === null) {
			$app = $this->authapp;
		}
		
		if ($this->authapp->_id !== $app->_id && !$this->isAdmin) {
			throw new PublicException('No permissions to access this page', 403);
		}
		
		if ($app === null) {
			throw new PublicException('No application found', 404);
		}
		
		$query = db()->table(connection\ContextModel::class)->get('app', $app)->group()->where('expires', '>', time())->where('expires', null)->endGroup();
		$pagination = new Paginator($query);
		
		$this->view->set('records', $pagination->records());
		$this->view->set('pag', $pagination);
		$this->view->set('app', $app);
	}
	
	/**
	 * Creates a context. A context allows an application to "fence off" certain
	 * parts of the data it contains and ensure that the user grants sharing this 
	 * information with external applications before doing so.
	 * 
	 * Originally contexts would expire regularly. But it seems to me that the idea
	 * that contexts expire was not really meaningful at the point.
	 * 
	 * Generally, the user should not be in a position of mistrust of the application
	 * that creates a context. So, when the application wishes to override the 
	 * context, it doesn't seem like it's useful to keep a record to ensure that
	 * the user can understand whether the application changed the description.
	 * 
	 * This would only be useful if the text was provided by a party that the user
	 * is supposed to not trust.
	 * 
	 * @throws PublicException
	 */
	public function create() {
		/*
		 * Get the context that the application is pretending to create.
		 */
		$context   = isset($_GET['context'])? $_GET['context'] : null;
		
		if (!$this->authapp && !$this->isAdmin) {
			throw new PublicException('Application or administrative authentication required for this endpoint', 401);
		}
		
		/*@var $record ContextModel*/
		$record = db()->table(connection\ContextModel::class)->get('ctx', $context)->where('app', $this->authapp)->first()?: db()->table(connection\ContextModel::class)->newRecord();
		$record->ctx     = $context;
		$record->app     = $this->authapp;
		$record->title   = _def($_POST['name'], 'Unnamed context');
		$record->descr   = _def($_POST['description'], 'Missing description');
		$record->expires = isset($_POST['expires'])? $_POST['expires'] + time() : null;
		$record->store();
		
		$this->view->set('result', $record);
	}
	
	/**
	 * 
	 * @validate >> POST#title(string required length[3,30]) AND POST#description(string required)
	 * @validate >> POST#ctx#Context(string required length[5, 50])
	 * 
	 * @param string $ctx
	 * @throws PublicException
	 */
	public function edit(ContextModel$ctx) {
		if (!($this->isAdmin || ($this->authapp && $this->authapp->appID === $ctx->app->appID))) {
			throw new PublicException('You do not have enough access privileges', 403);
		}
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not posted'); }
			if (!$this->validation->isEmpty()) { throw new ValidationException('Validation failed', 1806181147, $this->validation->toArray()); }
			
			$ctx->title = $_POST['title'];
			$ctx->descr = $_POST['description'];
			$ctx->ctx   = $_POST['ctx'];
			$ctx->store();
			
			return $this->response->setBody('Redirecting...')->getHeaders()->redirect(url('context', 'edit', $ctx->_id));
		}
		catch (spitfire\exceptions\HTTPMethodException$e) {
			//Just show the form
		}
		catch (spitfire\validation\ValidationException$e) {
			$this->view->set('messages', $e->getResult());
		}
		
		$this->view->set('context', $ctx);
	}
	
	public function deny(AuthModel$auth) {
		
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
		
		$record = db()->table(connection\AuthModel::class)->newRecord();
		$record->source = $auth->source;
		$record->target = $auth->target;
		$record->user   = $this->user;
		$record->state  = AuthModel::STATE_DENIED;
		$record->context= $auth->context;
		$record->expires= null;
		$record->final  = false;
		$record->store();
		
		$this->response->setBody('Redirecting...')->getHeaders()->redirect(url('permissions', 'on', $auth->target->_id));
	}
	
	public function revoke(AuthModel$auth) {
		
		if ($this->token || $this->authapp) {
			throw new PublicException('Insufficient context', 403);
		}
		
		if ($this->isAdmin || $auth->user->_id === $this->user->_id) {
			$auth->expires = time();
			$auth->store();
		}
		else {
			throw new PublicException('No permissions to edit this context', 403);
		}
		
		$this->response->setBody('Redirecting...')->getHeaders()->redirect(url('permissions', 'on', $auth->target->_id));
	}
	
	public function destroy(ContextModel$ctx) {
		
		if (!$this->isAdmin && !$this->authapp->appID === $ctx->app->appID) {
			throw new PublicException('Forbidden', 403);
		}
		
		$ctx->expires = time();
		$ctx->store();
		
		if (isset($_GET['returnto'])) {
			return $this->response->setBody('Redirecting...')->getHeaders()->redirect($_GET['returnto']);
		}
	}
	
	public function granted(ContextModel$ctx) {
		
		if (!$this->isAdmin) {
			throw new PublicException('Forbidden', 403);
		}
		
		$grants = db()->table(connection\AuthModel::class)
			->get('source', $ctx->app)
			->where('context', $ctx->ctx)
			->where('user', null)
			->group()->where('expires', null)->where('expires', '>', time())->endGroup()
			->all();
			
		$this->view->set('context', $ctx);
		$this->view->set('grants', $grants);
	}
	
	/**
	 * 
	 * @validate GET#app(required positive number) AND GET#grant(required positive number)
	 * @param ContextModel $ctx
	 */
	public function grant(ContextModel$ctx) {
		
		if (!$this->isAdmin) {
			throw new PublicException('Forbidden', 403);
		}
		
		if ($_GET['grant'] == AuthModel::STATE_PENDING) {
			throw new PublicException('Generalized grants cannot be set to pending. This is the default', 400);
		}
		
		$grant = db()->table(connection\AuthModel::class)
			->get('source', $ctx->app)
			->where('context', $ctx->ctx)
			->where('target', db()->table('authapp')->get('_id', $_GET['app']))
			->group()->where('expires', null)->where('expires', '>', time())->endGroup()
			->first()? : db()->table(connection\Authmodel::class)->newRecord();
		
		$grant->source  = $ctx->app;
		$grant->target  = db()->table('authapp')->get('_id', $_GET['app'])->first();
		$grant->user    = null;
		$grant->state   = $_GET['grant'];
		$grant->context = $ctx->ctx;
		$grant->expires = null;
		$grant->final   = isset($_GET['final']) && $_GET['final'] !== '0';
		$grant->store();
		
		return $this->response->setBody('Redirecting...')->getHeaders()->redirect(url('context', 'granted', $ctx->_id));
		
	}
	
}
