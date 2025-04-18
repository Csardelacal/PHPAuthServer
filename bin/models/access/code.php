<?php namespace access;

use spitfire\Model;
use Reference;
use spitfire\storage\database\Schema;

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
 *
 * @see https://www.oauth.com/oauth2-servers/accessing-data/authorization-request/
 */
class CodeModel extends Model
{
	
	/**
	 *
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema)
	{
		$schema->client    = new \Reference(\AuthAppModel::class);
		
		/**
		 * The audience needs to be defined in the code section of the authentication
		 * process so the usr can make an informed decision about whether they wish to
		 * grant access to a specific application for a specific audience.
		 */
		$schema->audience  = new \Reference(\AuthAppModel::class);
		
		/**
		 * The resource owner. This user is left unpopulated until the application can
		 * authenticate the user and collect their consent.
		 */
		$schema->user      = new \Reference('user');
		
		$schema->code      = new \StringField(255);
		
		/*
		 * The state is a known oAuth parameter used to prevent a user from being
		 * attacked by CSRF. An attacker cannot mislead a victim to a authentication
		 * dialog that provides them access to resources they did not intend to access.
		 *
		 * In the documentation this is explained as a scenario in which an attacker
		 * directs the user to a link that authenticates them to the wrong account in
		 * an application that requires them to enter their banking details to buy
		 * goods, allowing the attacker to log into the account and retrieve the
		 * credentials.
		 */
		$schema->state = new \StringField(255);
		
		/*
		 * Provides information about the challenge the user needs to solve in
		 * order to access the code which it can trade for the token.
		 */
		$schema->challenge = new \StringField(255);
		
		$schema->scopes = new \StringField(255);
		
		/*
		 * The redirect needs to be a valid redirect within the client application.
		 * This will be checked using the client's locations.
		 */
		$schema->redirect = new \StringField(1024);
		
		/*
		 * The session is associated with the login attempt so once this code is traded
		 * for a token, the token can be properly associated with the session.
		 */
		$schema->session = new Reference('session');
		
		$schema->created = new \IntegerField(true);
		$schema->expires = new \IntegerField(true);
	}
}
