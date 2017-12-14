<?php

use connection\ContextModel;
use spitfire\exceptions\PublicException;

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
	
	public function index($appid) {
		
	}
	
	public function create() {
		/*
		 * Get the signature and the context that the application is pretending 
		 * to create.
		 */
		$signature = isset($_GET['signature'])? $_GET['signature'] : '';
		$context   = isset($_GET['context'])? $_GET['context'] : null;
		
		/*
		 * Extract the appropriate information from the signature. Signatures often
		 * contain more data than necessary.
		 */
		list($algo, $src, $target, $ignore, $salt, $hash) = Signature::extract($signature);
		
		/*
		 * Get the application's secret. This will help us verify whether the 
		 * application is actually itself.
		 */
		$app = db()->table('authapp')->get('appID', $src)->fetch();
		$calculated = new Signature($algo, $src, $app->appSecret);
		
		/*
		 * Signatures can define a target. Target enriched signatures are rejected,
		 * since they are transmitted to third parties to authenticate the app
		 * against those third parties.
		 */
		if ($target) {
			throw new PublicException('This endpoint does not accept targets', 400);
		}
		
		/*
		 * Check the signature to ensure that the application is identifying itself
		 * properly.
		 */
		if (!$calculated->salt($salt)->verify($hash)) {
			throw new PublicException('Invalid signature', 403);
		}
		
		/*@var $record ContextModel*/
		$record = db()->table('connection\context')->newRecord();
		$record->ctx     = $context;
		$record->app     = $app;
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
	
}