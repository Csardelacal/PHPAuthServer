<?php

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

class PasswordController extends BaseController
{
	
	public function verify($passwordid) {
		
		if (!$this->user) {
			$this->response->setBody('Redirecting...')->getHeaders()->redirect(url());
			return;
		}
		
		foreach ($this->level as $level) {
			if ($level->getAuthId() == $passwordid) { throw new PublicException('Already authorized', 403); }
		}
		
		$provider = db()->table('authentication\provider')->get('_id', $passwordid)->first(true);
		
		if (db()->table('authentication\challenge')->get('provider', $provider)->where('expires', '>', time())->first()) {
			throw new PublicException('Challenge is not available', 400);
		}
		
		if ($provider->user->_id != $this->user->_id) {
			throw new PublicException('Not allowed', 403);
		}
		
		/*
		 * If there is no password hashing mechanism, we should abort ASAP. Since 
		 * nothing the application could do then would make any sense.
		 */
		if (!function_exists('password_hash')) 
			{ throw new PrivateException('Password hashing algorithm is missing. Please check your PHP version', 1602270012); }
		
		/*
		 * If the password doesn't match, then we need to tell the user that whatever
		 * he wrote into the form was not acceptable.
		 */
		if (!password_verify($_POST['password'], $provider->content)) { 
			$this->view->set('error', 'wrong');
			die('wrong password');
			return; 
		}
		
		/*
		 * Getting here means the password was correct, we can now ensure that it's
		 * up to speed with the latest encryption and rehash it in case it's needed.
		 */
		if (password_needs_rehash($provider->content, PASSWORD_DEFAULT)) {
			$provider->content = password_hash($_POST['password'], PASSWORD_DEFAULT);
			$provider->store();
		}
		
		/*@var $levels \spitfire\core\Collection*/
		$challenge = authentication\ChallengeModel::make($provider);
		$challenge->cleared = time();
		$challenge->store();
		
		$this->response->setBody('Redirect')->getHeaders()->redirect($_GET['returnto']);
	}
	
}
