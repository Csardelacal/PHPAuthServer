<?php namespace mfa;

use authentication\ProviderModel;
use BaseController;
use passport\PhoneUtils;
use PassportModel;
use ReflectionClass;
use twofactor\sms\Message;
use spitfire\core\Environment;
use spitfire\core\http\URL;
use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
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

class PhoneController extends BaseController
{
	
	
	
	/**
	 * 
	 * @validate >> POST#phone(string required)
	 * @throws HTTPMethodException
	 */
	public function create() {
		
		if (!$this->user) {
			throw new PublicException('Login required', 401);
		}
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not Posted'); }
			if (!$this->validation->isEmpty()) { throw new ValidationException('Validation failed', 2005121355, $this->validation->toArray()); }
			
			/*
			 * For the sake of validation, we canonicalize phone numbers before searching
			 * for duplicates. This prevents users from abusing the system.
			 */
			$canonical = PhoneUtils::canonicalize($_POST['phone'], true);
			
			/*
			 * If somebody is already using this phone number as a login mechanism,
			 * we need to tell the user that we cannot accept another login with this
			 * number.
			 */
			if (db()->table('passport')->get('canonical', $canonical)->where('type', PassportModel::TYPE_PHONE)->where('expires', null)->first()) { 
				throw new ValidationException('Validation failed', 2005121355, ['Phone is already registered']); 
			}
			
			/*
			 * Only one user can register a certain telephone number as a passport
			 * (meaning they can log into the system using the phone number and email, and SMS
			 * that are sent to other applications identify the user).
			 */
			if (isset($_POST['login']) && $_POST['login'] !== false) {
				/*
				 * Register the phone as a passport to log into the system. This allows
				 * the user to enter their phone number instead of their username if
				 * wanted.
				 */
				$passport = db()->table('passport')->newRecord();
				$passport->user = $this->user;
				$passport->type = PassportModel::TYPE_PHONE;
				$passport->content = $_POST['phone'];
				$passport->canonical = $canonical;
				$passport->login = true;

				/*
				 * If the user does not verify the number within 30 days, we will remove 
				 * the record. 
				 */
				$passport->expires = time() + 86400 * 30;
				$passport->store();
			}
			
			$auth = db()->table('authentication\provider')->newRecord();
			$auth->user = $this->user;
			$auth->type = ProviderModel::TYPE_PHONE;
			$auth->passport = $passport;
			$auth->content = $canonical;
			$auth->lastUsed = time();
			$auth->expires = time() + 86400 * 30;
			$auth->store();
			
			$this->response->setBody('Redirection...')->getHeaders()->redirect(url(['mfa', 'phone'], 'challenge', $auth->_id));
		} 
		catch (HTTPMethodException $ex) {
			# Do nothing, just show the form to the user
		}
	}
	
	/**
	 * Removes an authentication provider, this prevents the provider from being
	 * used in the future. This won't work for passwords.
	 * 
	 * @todo This method can be used to remove any provider, maybe merge?
	 * @param ProviderModel $provider
	 * @throws PublicException
	 */
	public function remove(ProviderModel$provider) {
		
		if (!$this->user) {
			throw new PublicException('Login required to remove phone numbers', 401);
		}
		
		$strength = $this->level;
		$expected = $this->user->mfa? 2 : 1;
		
		if ($strength->count() < $expected) {
			return $this->response->setBody('Redirect...')->getHeaders()
				->redirect(url('auth', 'threshold', $expected, ['returnto' => strval(url(['mfa', 'phone'], 'remove', $provider->_id))]));
		}
		
		if ($provider->user->_id != $this->user->_id) {
			throw new PublicException('You cannot remove phone numbers for other users', 403);
		}
		
		if ($provider->type != ProviderModel::TYPE_PHONE) {
			throw new PublicException('You can only use this endpoint to remove phone numbers', 403);
		} 
		
		$passport = $provider->passport;
		
		if ($passport) { 
			$passport->expires = time(); 
			$passport->store();
		}
		
		$provider->expires = time();
		$provider->store();
		
		$this->view->set('provider', $provider);
		$this->view->set('passport', $passport);
	}
	
	/**
	 * Executes a challenge against the selected phone. The phone will be sent an
	 * SMS message that contains a code which the user has to put back into the
	 * system to unlock the provider.
	 * 
	 * @param ProviderModel $phone
	 * @throws PublicException
	 * @throws PrivateException
	 */
	public function challenge(ProviderModel $phone) 
	{
		
		/**
		 * If the user doesn't have a session, the application should not let
		 * them continue and instead direct them to a log-in dialog.
		 */
		if (!$this->session) {
			$this->response->setBody('Redirecting')->getHeaders()->redirect(url('user', 'login', ['returnto' => strval(URL::current())]));
			return;
		}
		
		$user = $this->session->candidate;
		
		/**
		 * We must make sure that the user is attempting to authenticate their own
		 * account, and not someone else's.
		 */
		if ($user->_id != $phone->user->_id) {
			throw new PublicException('You are not authorized to use this provider', 401);
		}
		
		/*
		 * Whenever a user is able to select their provider, the system must make
		 * sure that the provider type we have is the right one.
		 * 
		 * Otherwise a user might be able to exploit a provider by passing the wrong
		 * type to the challenge method.
		 */
		if ($phone->type != ProviderModel::TYPE_PHONE) {
			throw new PublicException('Invalid provider', 400);
		}
		
		/*
		 * To avoid a user spamming the "send another SMS" button and draining the
		 * balance on the messaging provider's end, we rate limit to sending up to
		 * 3 SMS before stopping the user.
		 */
		if (db()->table('authentication\challenge')->get('provider', $phone)->where('expires', '>', time())->count() > 2) {
			throw new PublicException('Retry limit reached, please wait...', 400);
		}
		
		/*
		 * Create a challenge, the challenge will have a secret that the user needs
		 * to type into their browser.
		 */
		$twofactor = \authentication\ChallengeModel::make($phone);
		$e = Environment::get('twofactor.sms.provider');
		
		if (!$e) { throw new PrivateException('Invalid sms provider defined', 2006101058); }
		$config = explode(':', $e, 2);
		
		list($provider, $settings) = $config;
		
		$reflection = new ReflectionClass($provider);
		
		if (!$reflection->implementsInterface(\twofactor\sms\TransportInterface::class)) {
			throw new PrivateException('Invalid sms provider defined', 2006101059);
		}
		
		$instance = new $provider($settings);
		$payload = new Message($phone->content, 'Your authentication code is: ' . $twofactor->secret);
		
		if ($instance->deliver($payload)) {
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url(['mfa', 'phone'], 'verify', $phone->_id, ['returnto' => $_GET['returnto']?? '/']));
		}
		else {
			throw new PublicException('SMS Delivery error', 500);
		}
	}
	/**
	 * This method verifies the response from a SMS verification attempt. This method 
	 * is invoked after the user requested a challenge to attempt to verify that 
	 * the message has arrived.
	 * 
	 * @param ProviderModel $provider
	 * @param type $_secret
	 * @return type
	 * @throws HTTPMethodException
	 * @throws PublicException
	 * @throws ValidationException
	 */
	public function verify(ProviderModel$provider, $_secret = null) 
	{
		
		
		if ($provider->type != ProviderModel::TYPE_PHONE) {
			throw new PublicException('Invalid provider', 400);
		}
		
		try {
			if (!$this->request->isPost() && !$_secret) { throw new HTTPMethodException('Not posted'); }
			if ($provider->expires !== null && $provider->expires < time()) { throw new PublicException('This provider has already expired', 403); }
			
			sleep(2);
			
			$secret = db()->table('authentication\challenge')->get('provider', $provider)->where('secret', $_secret?: $_POST['secret'])->where('expires', '>', time())->first(true);
			if ($secret->secret != ($_secret?: $_POST['secret'])) { throw new ValidationException('Invalid secret', 0, ['Invalid secret']); }
			
			/*
			 * Check if the passport was already recorded. If this is the case, the 
			 * user cannot confirm this as their own.
			 */
			$exists = db()->table('passport')
				->get('canonical', $provider->canonical)
				->where('user', '!=', $provider->user)
				->first();
			
			if ($provider->expires && $provider->passport && $exists) {
				throw new PublicException('The provider is already reserved for another user', 403);
			}
			
			/*
			 * Since the user managed to successfully authenticate this provider, we
			 * assume that the user wishes to use this for further authentication.
			 */
			$preferred = db()->table('authentication\provider')->get('user', $provider->user)->where('preferred', true)->first();
			
			if ($preferred && $preferred->_id != $provider->_id) {
				$preferred->preferred = false;
				$preferred->store();
			}
			
			$provider->preferred = true;
			$provider->store();
			
			/*
			 * If the provider happened to not be verified yet, we will continue and 
			 * do so now.
			 */
			if ($provider->expires) {
				$provider->expires = null;
				$provider->store();
			}
			
			/*
			 * If the passport was flagged to be expiring, then we remove the flag.
			 * This happens unless the passport was already expired.
			 */
			if ($provider->passport && $provider->passport->expires && $provider->passport->expires > time()) {
				$provider->passport->expires = null;
				$provider->passport->store();
			}
			
			$secret->cleared = time();
			$secret->store();
			
			
			$this->response->setBody('Redirect')->getHeaders()->redirect($_GET['returnto']?? url());
			return;
		} 
		catch (HTTPMethodException $ex) {

		}
		
		$this->view->set('provider', $provider);
	}
	
	
}
