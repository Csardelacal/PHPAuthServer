<?php namespace mfa;

use authentication\ChallengeModel;
use authentication\ProviderModel;
use BaseController;
use spitfire\core\http\URL;
use spitfire\exceptions\HTTPMethodException;
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

class BackUpCodeController extends BaseController
{
	
	public function index() 
	{
		# We don't need this endpoint right now.
		$this->response->setBody('Redirect...')->getHeaders()->redirect(url('twofactor'));
	}
	
	public function generate() 
	{
		/*
		 * If the user has not yet locked a session to their name, the application
		 * cannot continue.
		 */
		if (!$this->user) {
			$this->response->setBody('Redirect')->getHeaders()->redirect(url('user', 'login', ['returnto' => (string)URL::current()]));
		}
		
		if ($this->level->count() < ($this->user->mfa? 2 : 1)) {
			$this->response->setBody('Redirect')->getHeaders()->redirect(url('auth', 'threshold', ($this->user->mfa? 2 : 1), ['returnto' => (string)URL::current()]));
		}
		
		/*
		 * Find the backup-code provider for the user. Since a user can only have
		 * one set of backup-codes, this measure just ensures that there is one
		 * we can attach the codes to.
		 */
		$provider = db()->table('authentication\provider')->get('user', $this->user)->where('type', ProviderModel::TYPE_CODES)->first();
		
		if (!$provider) {
			$provider = db()->table('authentication\provider')->newRecord();
			$provider->user = $this->user;
			$provider->type = ProviderModel::TYPE_CODES;
			$provider->lastUsed = time();
			$provider->created = time();
			$provider->store();
		}
		
		/*
		 * Whenever the user requests to generate a new set of codes, the old ones
		 * must be immediately discarded to ensure the system does not pollute
		 * anything with potentially invalid codes.
		 */
		db()->table('authentication\challenge')->get('provider', $provider)->all()->each(function (ChallengeModel $challenge) {
			$challenge->delete();
		});
		
		/*
		 * This array will contain the secrets that we must flash so the user can
		 * save them in a secure spot before we never speak of them again.
		 */
		$flash = [];
		
		/*
		 * Generate a set of new challenges. These can then be used to authenticate
		 * the user in future situations.
		 */
		for ($i = 0; $i < 10; $i++) {
			$random = bin2hex(random_bytes(16));
			
			$challenge = db()->table('authentication\challenge')->newRecord();
			$challenge->provider = $provider;
			$challenge->secret = sprintf('%s-%s-%s-%s', substr($random, 0, 8), substr($random, 8, 8), substr($random, 16, 8), substr($random, 24, 8));
			$challenge->created = time();
			$challenge->store();
			
			$flash[] = $challenge->secret;
		}
		
		/*
		 * Pass the data we wish to flash to the view. This can display the data
		 * once.
		 */
		$this->view->set('flash', $flash);
	}
	
	public function challenge()
	{
		
		if (isset($_GET['returnto']) && \Strings::startsWith($_GET['returnto'], '/')) { $returnto = $_GET['returnto']; }
		else { $returnto = (string)url('twofactor'); }
		
		/*
		 * If the user has not yet locked a session to their name, the application
		 * cannot continue.
		 */
		if (!$this->session) {
			$this->response->setBody('Redirect')->getHeaders()->redirect(url('user', 'login', ['returnto' => (string)URL::current()]));
		}
		
		/*
		 * Find the backup-code provider for the user. If the query returns no result
		 * it means that the user had the backup code functionality disabled, and 
		 * therefore they cannot be used to authenticate the user.
		 */
		$provider = db()->table('authentication\provider')->get('user', $this->session->candidate)->where('type', ProviderModel::TYPE_CODES)->first(true);
		
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not posted'); }
			
			$challenge = db()->table('authentication\challenge')
				->get('provider', $provider)
				->where('secret', trim($_POST['secret']))
				->where('cleared', null)
				->first(function () { throw new ValidationException('Validation failed', 0, []); });
			
			$challenge->expires = time() + 3600;
			$challenge->cleared = time();
			$challenge->session = $this->session;
			$challenge->store();
			
			$this->response->setBody('Redirect...')->getHeaders()->redirect($returnto);
		} 
		catch (HTTPMethodException $ex) {
			/* The form can be rendered for get requests */
		}
		catch (ValidationException $e) {
			/* The user didn't manage to enter a code that was considered to be valid */
			$this->view->set('error', 'Code was not valid');
		}
	}
	
	/**
	 * Allows the user to revoke all the backup codes, and to disable this mechanism
	 * completely. It will no longer be offered as a authentication mechanism to 
	 * people attempting to log into the account.
	 */
	public function disable() {
		
		/*
		 * If the user has not yet locked a session to their name, the application
		 * cannot continue.
		 */
		if (!$this->user) {
			return $this->response->setBody('Redirect...')->getHeaders()->redirect(url('user', 'login'));
		}
		
		
		/**
		 * Check the user's authentication level.
		 */
		if ($this->level->count() < ($this->user->mfa? 2 : 1)) {
			$this->response->setBody('Redirect')->getHeaders()->redirect(url('auth', 'threshold', ($this->user->mfa? 2 : 1), ['returnto' => (string)URL::current()]));
		}
		
		/*
		 * Find the backup-code provider for the user. Since a user can only have
		 * one set of backup-codes, this measure just ensures that there is one
		 * we can attach the codes to.
		 */
		$provider = db()->table('authentication\provider')->get('user', $this->user)->where('type', ProviderModel::TYPE_CODES)->first();
		
		if (!$provider) {
			return $this->response->setBody('Redirect...')->getHeaders()->redirect(url('twofactor'));
		}
		
		/*
		 * We can clear all the challenges for this user, they won't need them once
		 * the provider is deleted.
		 */
		db()->table('authentication\challenge')->get('provider', $provider)->all()->each(function ($e) {
			$e->delete();
		});
		
		$provider->delete();
		
		return $this->response->setBody('Redirect...')->getHeaders()->redirect(url('twofactor'));
	}
}
