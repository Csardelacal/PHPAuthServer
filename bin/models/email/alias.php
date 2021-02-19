<?php namespace email;

use IntegerField;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;

/*
 * The MIT License
 *
 * Copyright 2019 César de la Cal Bretschneider <cesar@magic3w.com>.
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
 * An alias is a system that allows applications to consistently map incoming emails
 * to a single identifier that allows to keep track of a user while also not disclosing
 * their actual email address to an application.
 *
 * To send an email to an alias, the application should just need to prefix it's
 * index with a colon (":") that indicates that the message is indeed an alias.
 * Likewise, when the application receives an incoming message from an aliased
 * address, it should be prefixed accordingly.
 *
 * Meanwhile, user accounts could be using plain ID and usernames, and emails that
 * are kept plain-text (because the application provided PHPAS with the email)
 * are provided like that.
 *
 * NOTE: One email address may be linked to multiple aliases, depending on how the
 * user interacts with the application.
 *
 * @property string $email The email address to be aliased. Full email (including domain)
 * @property int $expires Timestamp indicating when the alias is no longer valid.
 *
 * @deprecated since version 0.1-dev
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class AliasModel extends Model
{
	
	/**
	 *
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema) {
		$schema->external = new StringField(150);
		$schema->internal = new StringField(250);
		$schema->expires = new IntegerField(true);
		
		$schema->index($schema->external, $schema->internal);
		$schema->index($schema->expires);
	}

}
