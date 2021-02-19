<?php namespace mfa;

use authentication\ProviderModel;
use BaseController;
use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PrivateException;
use spitfire\validation\ValidationException;
use function db;
use function url;

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

class PasswordController extends BaseController
{
	
	/**
	 * 
	 * @validate >> POST#password(length[8, 40] required string)
	 * @throws PrivateException
	 * @throws HTTPMethodException
	 * @throws ValidationException
	 */
	public function set() 
	{
		
		/*
		 * Setting the password requires the user to be properly authenticated so
		 * a stolen session cannot hijack an account.
		 */
		if (!$this->user) {
			#TODO: Redirect to login
		}
		
		#TODO: Require the user to be strongly authenticated to perform this action
		
		/*
		 * Fetch the authentication provider for the password. The user can only
		 * have one password on their account, so there's no need to qualify it.
		 * 
		 * In case the user had no password (which is a very weird condition, but 
		 * could be given if the administrator created an account for a user on
		 * a server that has no sign-up method)
		 */
		$provider = db()->table('authentication\provider')->get('user', $this->user)->where('type', ProviderModel::TYPE_PASSWORD)->first();
		
		if (!$provider) {
			$provider = db()->table('authentication\provider')->newRecord();
			$provider->user = $this->user;
			$provider->type = ProviderModel::TYPE_PASSWORD;
			$provider->preferred = true;
			$provider->created = time();
			$provider->store();
		}
		
		/*
		 * If there is no password hashing mechanism, we should abort ASAP. Since 
		 * nothing the application could do then would make any sense.
		 */
		if (!function_exists('password_hash')) 
			{ throw new PrivateException('Password hashing algorithm is missing. Please check your PHP version', 1604251501); }
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException(); }
			if (!$this->validation->isEmpty()) { throw new ValidationException('Password is invalid', 0, $this->validation->toArray()); }
			
			/*
			 * Hash and set the new password. Please note that this function does not
			 * invoke the store() function. This prevents the method from being called
			 * by accident.
			 */
			$provider->content = password_hash($_POST['password'], PASSWORD_DEFAULT);
			$provider->store();
			
			/*
			 * Once the password has been properly set, redirect the user to a success
			 * page.
			 */
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url('twofactor'));
		}
		catch (HTTPMethodException $ex) {
			/*Show the form*/
		}
		catch (ValidationException $e) {
			$this->view->set('messages', $e->getResult());
		}
		
	}
	
	/**
	 * 
	 * @validate >> POST#password(length[8, 40] required string)
	 * @throws PrivateException
	 * @throws HTTPMethodException
	 * @throws ValidationException
	 */
	public function challenge() 
	{
		
		#TODO: This needs to work with session candidates
		$user = $this->user;
		
		/*
		 * Fetch the authentication provider for the password. The user can only
		 * have one password on their account, so there's no need to qualify it.
		 * 
		 * In case the user had no password (which is a very weird condition, but 
		 * could be given if the administrator created an account for a user on
		 * a server that has no sign-up method)
		 */
		$provider = db()->table('authentication\provider')->get('user', $user)->where('type', ProviderModel::TYPE_PASSWORD)->first();
		
		/*
		 * If there is no password hashing mechanism, we should abort ASAP. Since 
		 * nothing the application could do then would make any sense.
		 */
		if (!function_exists('password_hash')) 
			{ throw new PrivateException('Password hashing algorithm is missing. Please check your PHP version', 1604251501); }
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException(); }
			if (!$this->validation->isEmpty()) { throw new ValidationException('Password is invalid', 0, $this->validation->toArray()); }
			
			/*
			 * Hash and set the new password. Please note that this function does not
			 * invoke the store() function. This prevents the method from being called
			 * by accident.
			 */
			/*
			 * If the password doesn't match, then we need to tell the user that whatever
			 * he wrote into the form was not acceptable.
			 */
			if (!password_verify($_POST['password'], $provider->content)) { throw new ValidationException('Password is invalid', 0, ['Bad password']); }

			/*
			 * Getting here means the password was correct, we can now ensure that it's
			 * up to speed with the latest encryption and rehash it in case it's needed.
			 */
			if (password_needs_rehash($provider->content, PASSWORD_DEFAULT)) {
				$provider->content = password_hash($_POST['password'], PASSWORD_DEFAULT);
				$this->store();
			}
			
			$challenge = db()->table('authentication\challenge')->newRecord();
			$challenge->user = $user;
			$challenge->type = ProviderModel::TYPE_PASSWORD;
			#TODO: The challenge needs to be locked to the session authenticating the user
			//$challenge->session = ;
			$challenge->cleared = time();
			$challenge->store();
			
			/*
			 * Once the password has been properly set, redirect the user to a success
			 * page.
			 */
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url('twofactor'));
		}
		catch (HTTPMethodException $ex) {
			/*Show the form*/
		}
		catch (ValidationException $e) {
			$this->view->set('messages', $e->getResult());
		}
		
		$this->view->set('user', $user);
		
	}
}
