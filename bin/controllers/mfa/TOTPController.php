<?php

namespace mfa;

use authentication\ProviderModel;
use BaseController;
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

class TOTPController extends BaseController 
{
	
	public function pair() 
	{
		
		/**
		 * @todo This code needs to be moved somewhere outside the method body
		 */
		$base32encode = function ($data) {
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
			$mask = 0b11111;

			$dataSize = strlen($data);
			$res = '';
			$remainder = 0;
			$remainderSize = 0;

			for ($i = 0; $i < $dataSize; $i++) {
				$b = ord($data[$i]);
				$remainder = ($remainder << 8) | $b;
				$remainderSize += 8;
				while ($remainderSize > 4) {
					$remainderSize -= 5;
					$c = $remainder & ($mask << $remainderSize);
					$c >>= $remainderSize;
					$res .= $chars[$c];
				}
			}
			if ($remainderSize > 0) {
				$remainder <<= (5 - $remainderSize);
				$c = $remainder & $mask;
				$res .= $chars[$c];
			}

			return $res;
		};
		
		
		#TODO: Require the user to be strongly authenticated to perform this action
		
		/*
		 * If there is already a TOTP provider for this account, the user needs to 
		 * remove the old one.
		 */
		$provider = db()->table('authentication\provider')->get('user', $this->user)->where('type', ProviderModel::TYPE_TOTP)->first();
		
		if (!$provider) {
			$secret = random_bytes(18);
			
			$provider = db()->table('authentication\provider')->newRecord();
			$provider->user = $this->user;
			$provider->type = ProviderModel::TYPE_TOTP;
			$provider->content = base64_encode($secret);
			$provider->lastUsed = time();
			$provider->created = time();
			$provider->expires = time() + 86400 * 30;
			$provider->store();
		}
		elseif ($provider->expires === null) {
			throw new PublicException('Only one TOTP provider may be registered per account', 403);
		}
		else {
			$secret = base64_decode($provider->content);
		}
		
		
		
		$this->view->set('secret', $base32encode($secret));
	}
	
	public function challenge() 
	{
		
		
		/*
		 * If the user has not yet locked a session to their name, the application
		 * cannot continue.
		 */
		if (!$this->session) {
			$this->response->setBody('Redirect')->getHeaders()->redirect(url('user', 'login', ['returnto' => (string)URL::current()]));
		}
		
		/*
		 * Find the totp provider for the user. If the query returns no result
		 * it means that the user had the totp functionality disabled, and 
		 * therefore they cannot be used to authenticate the user.
		 * 
		 * We only allow ONE TOTP provider per user at this point.
		 */
		$provider = db()->table('authentication\provider')->get('user', $this->session->candidate)->where('type', ProviderModel::TYPE_TOTP)->first();
		
		if (!$provider) {
			throw new PublicException('No TOTP pairing found');
		}
		
		#TODO: Extract TOTP mechanism so a bunch of valid codes can be generated for the user. So, users on the fence have no issues logging in
		/*
		 * The epoch is the amount of 30 second slots that passed since the unix timestamp
		 * value of 0.
		 */
		$secret = base64_decode($provider->content);
		$epoch = intval(time() / 30);
		$packed = [0, 0, 0, 0, 0, 0, 0, 0];
		
		/*
		 * The integer of the epoch gets converted into an array of byte strings.
		 * But for some reason it gets packed backwards.
		 */
		for ($i = 0; $i < 8; $i++) {
			$packed[7 - $i] = pack('C*', $epoch);
			$epoch = $epoch >> 8;
		}
		
		/*
		 * Calculate the sha1 hmac on the result of our packing.
		 */
		$sha1 = hash_hmac('sha1', implode($packed), $secret, true);
		
		/*
		 * Get the offset to be used from our hmac. We use the last 4 bit of the 
		 * hmac to determine where to truncate from.
		 */
		$length = strlen($sha1);
		$offset = ord($sha1[$length - 1]) & 0xF;
		
		/*
		 * Extract the bytes at the offset, and omit the first bit so it fits into 
		 * 31 bits. This prevents the code from causing issues with 4byte signed and
		 * unsigned integers.
		 */
		$truncated = (ord($sha1[$offset++]) & 0x7F) << 24 |
				  (ord($sha1[$offset++]) & 0xFF) << 16 |
				  (ord($sha1[$offset++]) & 0xFF) << 8 |
				  (ord($sha1[$offset++]) & 0xFF);

		$totp = $truncated % pow(10, 6);
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException();}
			if ( intval($_POST['challenge']) !== intval($totp)) { throw new ValidationException('Code failed', 0, []); }
			
			$challenge = db()->table('authentication\challenge')->newRecord();
			$challenge->session = $this->session;
			$challenge->provider = $provider;
			$challenge->cleared = time();
			$challenge->expires = time() + 1200;
			$challenge->store();
			
			#TODO: Add flag for whether the provider is active
			$provider->expires = null;
			$provider->store();
			
			$this->response->setBody('Redirect')->getHeaders()->redirect(url('twofactor'));
		}
		catch (HTTPMethodException $ex) {
			
		}
		catch (ValidationException $ex) {
			$this->view->set('messages', [$ex->getMessage()]);
		}
		
		$this->view->set('challenge', $totp);
	}
	
	public function remove() 
	{
		
		if (!$this->user) {
			throw new PublicException('Login required to remove phone numbers', 401);
		}
		
		$strength = $this->level;
		$expected = $this->user->mfa? 2 : 1;
		
		/*
		 * Our system only allows one TOTP pairing per account for now, this prevents
		 * the system from being fuzzy about which providers it offers.
		 */
		$provider = db()->table('authentication\provider')->get('user', $this->user)->where('type', ProviderModel::TYPE_TOTP)->first();
		
		if ($strength->count() < $expected) {
			return $this->response->setBody('Redirect...')->getHeaders()
				->redirect(url('auth', 'threshold', $expected, ['returnto' => strval(url(['mfa', 'totp'], 'remove', $provider->_id))]));
		}
		
		$provider->delete();
		
		$this->response->setBody('Redirect...')->getHeaders()->redirect(url('twofactor'));
	}
}
