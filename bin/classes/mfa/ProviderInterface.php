<?php namespace mfa;

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
 * Whenever a user wishes to confirm their identity using multi factor authentication,
 * the system will generate a challenge and verify whether the user passed it.
 * 
 * Providers wishing to integrate with PHPAS are required to implement this interface
 * to be able to challenge users.
 * 
 * @deprecated since the day it was written
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
interface ProviderInterface
{
	
	/**
	 * Currently, the system can generate two types of challenge for the user. Either
	 * they display a dialog to collect the user's input to respond to the challenge,
	 * a script that executes on their device to execute, or a wait prompt which indicates 
	 * the user to perform or verify some communication.
	 * 
	 * Examples of the last MFA challenge (wait) are applications that just indicate the user to 
	 * wait for a prompt on their mobile phone through an app. As soon as they receive
	 * the prompt, and confirm the login they can continue.
	 * 
	 * Most MFA challenges rely on out-of-band communication with the user, and 
	 * therefore expect the user to input a code or to verify themselves by some other mechanism.
	 * 
	 * @todo This mechanism does not work with passwords, which need to rewrite themselves in the event of being invalidated
	 * 
	 * @param integer $id A process identifier. This is NOT cryptographically secure.
	 * @param string  $payload The location the user defined as target (email, phone number, password hash,...)
	 * @return ChallengeInterface
	 */
	public function challenge($id, $payload) : ChallengeInterface;
	
	/**
	 * The user or their device needs to convert the challenge into a string that
	 * can be verified by this method.
	 * 
	 * @param integer $id
	 * @param string $payload The location the user defined as target (email, phone number, password hash,...)
	 * @param string $response The response the user / their device generated to the challenge
	 * @return bool Whether the challenge was successful.
	 */
	public function verify($id, $payload, $response);
	
}
