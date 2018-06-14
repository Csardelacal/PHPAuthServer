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
	
	public function for(AuthAppModel$app) {
		if (!$this->user) {
			throw new PublicException('You must be logged in to perform this action', 403);
		}
		
		if ($this->token) {
			throw new PublicException('This action cannot be performed in token context', 403);
		}
		
		$connections = db()->table('connection\auth')
			->get('target', $app)
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
		
		
		$attributes = db()->table('attribute')->getAll()->all();
		
		$this->view->set('app',  $app);
		$this->view->set('attributes', $attributes);
		$this->view->set('connections', $connections);
	}
	
	/**
	 * 
	 * @validate GET#grant(required number)
	 * @param AttributeModel $attribute
	 * @param type $appId
	 */
	public function set(AttributeModel$attribute, $appId) {
		$app  = db()->table('authapp')->get('appID', $appId)->first(true);
		$xsrf = new spitfire\io\XSSToken();
		
		try {
			$xsrf->verify($_GET['_XSRF']);
			
			$record = db()->table('attribute\appgrant')
				->get('app', $app)
				->where('user', $this->user)
				->where('attribute', $attribute)
				->first();
			
			if (!$record) {
				$record = db()->table('attribute\appgrant')->newRecord();
				$record->app = $app;
				$record->user = $this->user;
				$record->attribute = $attribute;
			}
			
			$record->grant = $_GET['grant'];
			$record->store();
			
			return $this->response->setBody('Redirect...')->getHeaders()->redirect($_GET['returnto']?? url());
		}
		catch (Exception$e) {
			
		}
	}
}