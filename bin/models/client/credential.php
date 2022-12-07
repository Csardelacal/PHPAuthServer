<?php namespace client;

use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;

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
 * A credential for an application is a secret that it can use to authenticate
 * itself against PHPAS and request an access token to log the user into an
 * account.
 *
 * @property \AuthAppModel $client The client that this credential authenticates
 * @property string $secret The secret the application can use to authenticate itself
 * @property integer $created The timestamp the secret was created, it's recommended to phase out old secrets regularly
 * @property integer $expires The timestamp this secret expires at. This allows PHPAS to provide the clients with a grace period
 */
class CredentialModel extends Model
{
	
	/**
	 *
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema)
	{
		$schema->client = new \Reference(\AuthAppModel::class);
		$schema->secret  = new StringField(255);
		$schema->created = new \IntegerField(true);
		$schema->expires = new \IntegerField(true);
	}
	
	/**
	 * In case the credentials were just created, we enrich them with a creation
	 * timestamp. This ensures we know how long the credential has been available
	 * and allows the application to warn the user about old and risky credentials.
	 *
	 * @return void
	 */
	public function onbeforesave(): void
	{
		parent::onbeforesave();
		
		if (!$this->created) {
			$this->created = time();
		}
		
		if (!$this->secret) {
			$this->secret = preg_replace('/[^a-z\d]/i', '', base64_encode(random_bytes(64)));
		}
	}
}
