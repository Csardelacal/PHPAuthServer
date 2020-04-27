<?php

use spitfire\exceptions\PublicException;

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

class PermissionsController extends BaseController
{
	
	public function index() {
		if (!$this->user) {
			throw new PublicException('You must be logged in to perform this action', 403);
		}
		
		if ($this->token) {
			throw new PublicException('This action cannot be performed in token context', 403);
		}
		
		$query = db()->table('authapp')->get('system', false);
		$pag   = new \spitfire\storage\database\pagination\Paginator($query);
		
		$this->view->set('apps', $pag);
	}
	
	public function on(AuthAppModel$app) {
		if (!$this->user) {
			throw new PublicException('You must be logged in to perform this action', 403);
		}
		
		if ($this->token) {
			throw new PublicException('This action cannot be performed in token context', 403);
		}
		
		$connections = db()->table('connection\auth')
			->get('target', $app)
			->where('source', '!=', db()->table('authapp')->get('system', true))
			->group()->where('user', $this->user)->where('user', null)->endGroup()
			->group()->where('expires', null)->where('expires', '>', time())->endGroup()
			->all()
			->groupBy( function ($e) {
				return $e->app->_id . '_' . $e->context;
			})
			->each(function (\spitfire\core\Collection$c) {
				return $c->reduce(function (connection\AuthModel$c, connection\AuthModel$e) {
					if ($e->user && $e->final) { return $e; }
					if ($c->user && $c->final) { return $c; }
					if ($e->user && $e->state == 1) { return $e; }
					if ($c->user && $c->state == 1) { return $c; }
					if ($e->final) { return $e; }
					if ($c->final) { return $c; }
					if ($e->user ) { return $e; }
					if ($c->user ) { return $c; }
					return $e;
				}, $c->rewind());
			});
		
		
		
		$this->view->set('app',  $app);
		$this->view->set('connections', $connections);
	}
	
	/**
	 * 
	 * @template none
	 * @layout none
	 * @param type $appID
	 */
	public function deauthorize($appID) {
		$app  = db()->table('authapp')->get('_id', $appID)->fetch();
		$auth = db()->table('user\authorizedapp')->get('user', $this->user)->where('app', $app)->first(true);
		
		if ($auth) { $auth->delete(); }
		
		$this->response->getHeaders()->redirect(url());
	}
}