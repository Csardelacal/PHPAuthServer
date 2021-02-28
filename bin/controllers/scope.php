<?php

use client\ScopeModel;
use spitfire\exceptions\HTTPMethodException;

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
 * This controllers allows clients (or in this case hosts) to modify the scopes 
 * they provide. Other clients can then ask resource owners for permission to
 * use these scopes.
 * 
 * Please note that due to the quirky nature of spitfire, the scope names should
 * avoid ending in `.json`, `.xml` or `.html` (or any common file extensions). Since this 
 * may cause the router to strip it and use it as a response format.
 * 
 * If your application wishes to use scopes that include these specific strings,
 * you should make sure to add an additional segment to your scope name. Something
 * like data.export.json could become data.export.type-json
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class ScopeController extends BaseController
{
	
	
	/**
	 * Retrieves metadata about a scope. This allows applications to display 
	 * information to a user. This information is public, so the server will make
	 * no attempt to verify the permissions to access this.
	 * 
	 * To read these you need to appropriately prefix them. The scopes for another
	 * client are defined with app{appid}.{scope}
	 * 
	 * @param string $name
	 */
	public function get(string $name) 
	{
		
		/*
		 * Find the scope, we edit existing scopes. Scopes are not versioned,
		 * so updating a scope wil not require the users to re-grant the scope
		 * to any applications that already have it.
		 * 
		 * If this is desired, we should provide a mechanism to reset the scope
		 * for all applications.
		 */
		$scope = db()->table(ScopeModel::class)->get('identifier', $name)->first();
		$this->view->set('scope', $scope);
	}
	
	/**
	 * Set the scope for a client. A scope allows an application to "fence off" certain
	 * parts of the data it contains and ensure that the user grants sharing this 
	 * information with external applications before doing so.
	 * 
	 * @validate POST#caption (required string length[3, 50])
	 * @validate POST#description (required string length[3, 250])
	 * 
	 * @param string $name
	 * @throws HTTPMethodException
	 */
	public function set(string $name) 
	{
		
		/**
		 * Get the client that is authenticating the request.
		 */
		$client = $this->authapp;
		
		if (!$client || $this->user) { 
			throw new PublicException('This method is only accessible in client context', 401); 
		}
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException(); }
			
			/*
			 * The identifiers are "global" but are scoped to the applications by 
			 * prefixing the id of the application to the identifier.
			 */
			$identifier = sprintf('%s.%s', $client->appID, $name);
			
			/*
			 * Find the scope, we edit existing scopes. Scopes are not versioned,
			 * so updating a scope wil not require the users to re-grant the scope
			 * to any applications that already have it.
			 * 
			 * If this is desired, we should provide a mechanism to reset the scope
			 * for all applications.
			 */
			$scope = db()->table(ScopeModel::class)->get('identifier', $identifier)->first();
			
			/*
			 * Should the scope not exist, we create it for the application.
			 */
			if (!$scope) {
				$scope = db()->table(ScopeModel::class)->newRecord();
				$scope->identifier = $identifier;
			}
			
			/*
			 * If the application sent an icon to use for the scope, we write that
			 * to our icon relation and then expire the old one.
			 */
			if (isset($_POST['icon'])) {
				$icon = db()->table(IconModel::class)->newRecord();
				$icon->file = $_POST['icon']->store()->uri();
				$icon->store();
				
				#Expire the old icon
				$scope->icon->expires = time();
				$scope->icon->store();
				
				#Create a task to incinerate the old icon
				async(\defer\incinerate\IconTask::class, $scope->icon->_id);
				
				#Set the new one
				$scope->icon = $icon;
			}
			/*
			 * If the application did not provide any icon, we assume it wanted it
			 * removed
			 */
			else {
				$scope->icon =  null;
			}
			
			/*
			 * Update the scope to mirror what the application set.
			 */
			$scope->caption = $_POST['caption'];
			$scope->description = $_POST['description'];
			$scope->store();
			
			$this->view->set('scope', $scope);
		} 
		catch (HTTPMethodException $ex) {
			throw new PublicException('Invalid request method', 400);
		}
	}
	
	public function delete($name) 
	{
		
	}
}
