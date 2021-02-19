<?php namespace defer\incinerate;

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
 * This task removes a credential after is has been expired. If the task has not
 * yet been expired, the task is deferred to a later point in time.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class CredentialTask extends Task
{

	private $defer;

	public function __construct(TaskFactory $defer)
	{
		$this->defer = $defer;
	}

	public function body(): Result
	{
		$credential = db()->table(\client\CredentialModel::class)->get('_id', $this->getSettings())->first();
		
		/**
		 * If the credential was already removed manually, or by another task that
		 * was set on accomplishing the same, the task can stop. This is a success
		 * state.
		 */
		if (!$credential) {
			return new Result('Credential was already removed.');
		}
		
		/**
		 * The credential may have been restored. If this is the case, trying to
		 * remove it would cause the system to behave erratically.
		 */
		if ($credential->expires === null) 
		{
			return new Result('Credential has no expiration.');
		}

		/*
		 * If the session was not yet expired when the incinerator started, we will
		 * defer this task for later to ensure that the session is not removed prematurely.
		 */
		if ($credential->expires > time()) 
		{
			$this->defer->defer($credential->expires, $this);
			return new Result('Credential was not yet expired. Retrying later.');
		}

		/*
		 * The token is expired and can be properly deleted. This token was already
		 * expired, so the time of deletion is not relevant.
		 */
		$credential->delete();

		return new Result('Incinerated credential successfully.');
	}
}
