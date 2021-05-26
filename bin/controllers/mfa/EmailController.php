<?php namespace mfa;

use authentication\ChallengeModel;
use authentication\ProviderModel;
use BaseController;
use passport\PhoneUtils;
use PassportModel;
use Postal\Client as PostalClient;
use Postal\Error as PostalError;
use Postal\SendMessage as PostalMessage;
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

class EmailController extends BaseController
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
			$canonical = \mail\MailUtils::canonicalize($_POST['email'], true);
			
			/*
			 * If somebody is already using this phone number as a login mechanism,
			 * we need to tell the user that we cannot accept another login with this
			 * number.
			 */
			if (db()->table('passport')->get('canonical', $canonical)->where('type', PassportModel::TYPE_EMAIL)->where('expires', null)->first()) { 
				throw new ValidationException('Validation failed', 2005121355, ['Email is already registered']); 
			}
			
			/*
			 * Register the email as a passport to log into the system. This allows
			 * the user to enter their email address instead of their username.
			 */
			$passport = db()->table('passport')->newRecord();
			$passport->user = $this->user;
			$passport->type = PassportModel::TYPE_EMAIL;
			$passport->content = $_POST['email'];
			$passport->canonical = $canonical;
			$passport->login = true;

			/*
			 * If the user does not verify the number within 30 days, we will remove 
			 * the record. 
			 */
			$passport->expires = time() + 86400 * 30;
			$passport->store();
			
			$auth = db()->table('authentication\provider')->newRecord();
			$auth->user = $this->user;
			$auth->type = ProviderModel::TYPE_EMAIL;
			$auth->passport = $passport;
			$auth->content = $canonical;
			$auth->lastUsed = null;
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
			throw new PublicException('Login required to remove email addresses', 401);
		}
		
		$strength = $this->level;
		$expected = $this->user->mfa? 2 : 1;
		
		if ($strength->count() < $expected) {
			return $this->response->setBody('Redirect...')->getHeaders()
				->redirect(url('auth', 'threshold', $expected, ['returnto' => strval(url(['mfa', 'email'], 'remove', $provider->_id))]));
		}
		
		if ($provider->user->_id != $this->user->_id) {
			throw new PublicException('You cannot remove email addresses for other users', 403);
		}
		
		if ($provider->type != ProviderModel::TYPE_EMAIL) {
			throw new PublicException('You can only use this endpoint to remove email addresses', 403);
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
	 * @param ProviderModel $email
	 * @throws PublicException
	 * @throws PrivateException
	 */
	public function challenge(ProviderModel $email) 
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
		if ($user->_id != $email->user->_id) {
			throw new PublicException('You are not authorized to use this provider', 401);
		}
		
		/*
		 * Whenever a user is able to select their provider, the system must make
		 * sure that the provider type we have is the right one.
		 * 
		 * Otherwise a user might be able to exploit a provider by passing the wrong
		 * type to the challenge method.
		 */
		if ($email->type != ProviderModel::TYPE_EMAIL) {
			throw new PublicException('Invalid provider', 400);
		}
		
		/*
		 * To avoid a user spamming the "send another email" option in case they're
		 * having trouble logging in, we stop the system from generating any further
		 * codes.
		 */
		if (db()->table('authentication\challenge')->get('provider', $email)->where('expires', '>', time())->count() > 2) {
			throw new PublicException('Retry limit reached, please wait...', 400);
		}
		
		/*
		 * Create a challenge, the challenge will have a secret that the user needs
		 * to type into their browser.
		 */
		#TODO: Add redirect location to the challenge so the user can be forwarded to the appropriate location
		#Sending it with the email potentially leaks codes, tokens, etc that should not get into the wrong hands
		$twofactor = \authentication\ChallengeModel::make($email);
		$twofactor->secret = str_replace(['/', '+'], ['_', '-'], base64_encode(random_bytes(16)));
		$twofactor->store();
		
		/**
		 * Instance a new email transport. This allows PHPAS to send email to the clients.
		 * 
		 * @todo For the time being, we're directly going to depend on Postal here. Future revisions are
		 * very welcome to introduce a standard interface for email delivery.
		 */
		$postal = spitfire()->provider()->get(PostalClient::class);
		$payload = [];
		
		/**
		 * Create a message to be sent
		 * 
		 * @todo This could actually be wrapped in a defer, so the user gets an immediate response from
		 * us, and the email can be delivered in the background.
		 * 
		 * @todo A nicer email template for this would be a major boon.
		 */
		$message = new PostalMessage($postal);
		$message->to($email->content);
		$message->from(config('email.smtp.from'));
		$message->htmlBody('Your two factor authentication code is ' . $twofactor->secret . ' or <a href="' . url(EmailController::class, 'verify', $email->_id, $twofactor->secret, ['returnto' => $_GET['returnto']?? '/'])->absolute() . '">click here</a>');
		
		/**
		 * Send the user to a location where they can verify their challenge
		 */
		try {
			$message->send();
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url(['mfa', 'email'], 'verify', $twofactor->_id));
		}
		catch (PostalError $e) {
			throw new PublicException('Email Delivery error', 500);
		}
	}
	
	/**
	 * 
	 * @param ChallengeModel $challenge
	 * @param string $solution
	 */
	public function verify(ChallengeModel $challenge, string $secret = null)
	{
		/**
		 * Check if the user trying to solve the challenge is actually the person they
		 * claim to be.
		 */
		if ($challenge->provider->user->_id !== $this->session->candidate->_id) {
			throw new \spitfire\exceptions\PublicException('Not allowed', 403);
		}
		
		try {
			/**
			 * If the user is submitting the challenge, we attempt to check
			 * whether they succeeded at doing so.
			 */
			if (!$this->request->isPost() || $secret) { throw new HTTPMethodException(); }
			
			$secret = $secret || $_POST['secret']?? false;
			
			/**
			 * If the user could not solve the challenge, we throw an exception so
			 * the user interface can print an error message.
			 */
			if ($challenge->secret !== $secret) { 
				throw new ValidationException('Challenge failed. The code is not valid', 0, []);
			}
			
			/**
			 * If the email address was not yet verified by the user, we can do that now.
			 * This will remove the expiration and ensure that the user can log into the 
			 * application with this email whenever they want.
			 */
			if ($challenge->provider->expires) {
				$challenge->provider->expires = null;
				$challenge->provider->store();
			}
			
			if ($challenge->provider->passport && $challenge->provider->passport->expires) {
				$challenge->provider->passport->expires = null;
				$challenge->provider->passport->store();
			}
			
			$challenge->cleared = time();
			$challenge->expires = time() + 3600;
			$challenge->session = $this->session;
			$challenge->store();
			
			$this->response->setBody('Redirecting...')->getHeaders()->redirect($_GET['returnto']?? url());
			return;
		}
		catch (HTTPMethodException $e) {
			/**
			 * We do nothing about this exception, just show the user the form to enter 
			 * the data.
			 */
		}
		catch (ValidationException $e) {
			$this->view->set('messages', [$e->getMessage()]);
		}
	}
	
}
