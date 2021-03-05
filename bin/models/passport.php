<?php

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
 * The passport model supersedes what was once the username model. On previous
 * versions of PHPAS, we would look for an email OR a username and were always
 * limited by this.
 * 
 * Passports allow linking a username, email and phone (or even several thereof)
 * to a user account. When the user removes the email address, username, or phone - 
 * PHPAS will have to do the same.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class PassportModel extends spitfire\Model
{
	
	/*
	 * In theory, Passports could be anything we want. We could also be using custom
	 * tailored passports or smart card tokens for this.
	 * 
	 * A passport basically links a person to something unqiue: a username, a phone
	 * number, an email address, a smart card token or anything similar. 
	 * 
	 * But generally, the user will need either a username, email or phone to log
	 * into 90% of services. It would be nice to have an API to extend this in the
	 * future.
	 */
	const TYPE_USERNAME = 'username';
	const TYPE_EMAIL    = 'email';
	const TYPE_PHONE    = 'phone';
	
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		$schema->user      = new Reference('user');
		$schema->type      = new StringField(20);
		
		/*
		 * The content of a passport is the username / email address /etc that the
		 * user provided in an unaltered fashion.
		 */
		$schema->content   = new StringField(50);
		
		/*
		 * Canonicalizing a passport prevents users from using variations of a passport
		 * to trick the system. Most notably, you could verify dozens of accounts
		 * with the emails
		 * 
		 * test@test.com
		 * test+123@test.com
		 * test+spam@test.com
		 * 
		 * And all the email would be directed to a single inbox. While this is a
		 * strong feature from a usability perspective, it allows users to leverage
		 * the feature and exploit the system to register invalid accounts.
		 */
		$schema->canonical = new StringField(50);
		
		/*
		 * Indicates whether the passport can be used to log the user in. A user can
		 * therefore decide whether their phone number, email address or username
		 * can be used to log them in or whether this information is secret.
		 * 
		 * Please note that this does not disable password recovery via email.
		 * 
		 * The idea here is that privacy conscious users can prevent the system from
		 * associating the email address or a phone number to a user account when
		 * logging in, since the login form will match the username and automatically
		 * fill it in when logging into the system.
		 */
		$schema->login = new BooleanField();
		
		/*
		 * Passports can expire. This allows the system to exclude emails that have
		 * been recently used from being abused to generate invalid accounts. It also
		 * prevents usernames that have been used recently to be recycled.
		 * 
		 * This causes applications to potentially behave differently depending on
		 * the expiration. For example, the login will treat passports with an expiration
		 * date as revoked and not consider them. Meanwhile, the user registration
		 * will consider expired passports to be reserved and not reuse them.
		 */
		$schema->expires = new IntegerField();
		
		$schema->index($schema->type, $schema->content);
		$schema->index($schema->type, $schema->canonical);
		$schema->index($schema->expires);
	}
	
	public function __toString() {
		return $this->name;
	}

}
