<?php

use auth\Authentication;
use authentication\ProviderModel;
use spitfire\core\Collection;
use spitfire\core\Environment;
use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
use spitfire\io\session\Session;
use spitfire\validation\ValidationException;

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

class TwofactorController extends BaseController
{
	
	public function index() {
		
		if (!$this->user) {
			throw new PublicException('You need to log in to adjust your MFA settings', 403);
		}
		
		$this->view->set('enabled', $this->user->mfa);
		
		$this->view->set('emails', db()->table('authentication\provider')
			->get('user', $this->user)->where('type', ProviderModel::TYPE_EMAIL)
			->group()->where('expires', null)->where('expires', '>', time())->endGroup()
			->all());
		
		$this->view->set('passwords', db()->table('authentication\provider')
			->get('user', $this->user)->where('type', ProviderModel::TYPE_PASSWORD)
			->group()->where('expires', null)->where('expires', '>', time())->endGroup()
			->all());
		
		$this->view->set('phones', db()->table('authentication\provider')
			->get('user', $this->user)->where('type', ProviderModel::TYPE_PHONE)
			->group()->where('expires', null)->where('expires', '>', time())->endGroup()
			->all());
		
		$this->view->set('totp', db()->table('authentication\provider')
			->get('user', $this->user)->where('type', ProviderModel::TYPE_TOTP)
			->group()->where('expires', null)->where('expires', '>', time())->endGroup()
			->all());
		
		$this->view->set('codes', db()->table('authentication\provider')
			->get('user', $this->user)->where('type', ProviderModel::TYPE_CODES)
			->group()->where('expires', null)->where('expires', '>', time())->endGroup()
			->all());
	}
	
	/**
	 * Enables Multi-Factor-Authentication for the user. The account will then require
	 * the user to provide MFA to log into the account from a new device or a forgotten
	 * one.
	 * 
	 * In order to enable MFA, the user needs to add at least one MFA mechanism that
	 * is not the account password, or an email.
	 * 
	 * @throws PublicException
	 */
	public function enable() {
		/*
		 * If the user is not logged in, they can obviously not enable the MFA
		 * verification for their account.
		 */
		if (!$this->user) { throw new PublicException('Login required', 403); }
		if ($this->user->mfa) { throw new PublicException('MFA already enabled', 400); }
		
		/*
		 * Make a list of accepted MFA providers for the current user, if the user
		 * has no valid providers, we will need to direct them to a page to add one.
		 * 
		 * The system needs to distinguish between primary and secondary MFA providers. 
		 * 
		 * Primary providers are the first level of access control, they require 
		 * the user to know something. These are:
		 * - Email (email usually requires a password login, so we assume the user needs to know their password. Even if we send them a link to log in.)
		 * - Password
		 * 
		 * Secondary providers are the ones that require the user to have access to 
		 * something they own and which can accept an out-of-band communication like:
		 * - Phone
		 * - TOTP (RFC6238) devices
		 * - Security keys (webauthn)
		 * - Backup codes
		 * 
		 * In order to own account the user must have provided a primary provider.
		 * So, when talking about 2FA, we're referring to the secondary providers.
		 */
		$accepted  = Environment::get('phpauth.mfa.providers.secondary')? explode(',', Environment::get('phpauth.mfa.providers.secondary')) : ['phone', 'rfc6238', 'backup', 'webauthn'];
		
		$providers = db()->table('authentication\provider')
			->get('expires', null)
			->where('user', $this->user)
			->where('type', $accepted)
			->all();
		
		if ($providers->isEmpty()) {
			throw new PublicException('You must add a valid provider for MFA to be enabled');
		}
		
		/*
		 * If the user checks out, the system will set their account to have MFA
		 * enabled, and will from now on, require the user to perform MFA whenever
		 * the user attempts to log into from an unknown device or requested to 
		 * not be remembered.
		 */
		$this->user->mfa = true;
		$this->user->store();
	}
	
	public function disable() {
		
		if ($this->level->count() < 2) {
			$this->response->setBody('Redirecting...')->getHeaders()->redirect(url('auth', 'threshold', 2, ['returnto' => strval(url('twofactor', 'disable'))]));
			return;
		}
		
		$this->user->mfa = false;
		$this->user->store();
		
	}
	
}
