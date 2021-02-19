<?php namespace auth;

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

/**
 * An authentication allows the system to record the different providers to determine
 * whether a session is still secure.
 * 
 * For example, when a user enters a password, the _id of the authentication mechanism
 * is recorded (to prevent people from spoofing two factor authentication by just
 * using the same mechanism twice) and an expiration, so the system will refresh
 * this if the authentication was not strong.
 * 
 * An expiration does not imply the session for the user is terminated, it just 
 * will require the user to refresh the authentication. For example, if the user
 * wishes to add an email address to their account, they will be required to enter
 * their password again (if they haven't done so in the last 10 minutes).
 * 
 * Basically, locking a session to an account will require the user to initiate a 
 * request in which they claim a session, and then authenticate themselves strongly 
 * enough so the session::lock method can be accessed.
 * 
 * Depending on the server's settings this may be a two factor process or even 
 * more than that.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class Authentication implements \Serializable
{
	
	private $authId;
	private $expires;
	
	public function __construct($authId, $expires) {
		$this->authId = $authId;
		$this->expires = $expires;
	}
	
	public function getAuthId() {
		return $this->authId;
	}
	
	public function isExpired() {
		return time() > $this->expires;
	}
	
	public function serialize() {
		return serialize(['id' => $this->authId, 'expires' => $this->expires]);
	}
	
	public function unserialize($serialized) {
		$data = unserialize($serialized);
		$this->authId = $data['id'];
		$this->expires = $data['expires'];
	}

}
