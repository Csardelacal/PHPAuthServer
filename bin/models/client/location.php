<?php namespace client;

use BooleanField;
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
 * An application can define locations to whitelist so redirections can be checked 
 * and validated. This prevents the login form from becoming a wildcard redirect 
 * to potentially harmful applications.
 * 
 * If the application defines a return URL that does not match any of the application's
 * paths, we will inform the user about an invalid URL.
 * 
 * Should the application provide no return path, the system will attempt to find
 * a default location to redirect the user to.
 * 
 * @property string $protocol If the protocol is defined, the protocol must match the redirect
 * @property string $hostname If the hostname is provided, the hostname must match
 * @property string $path     If the path is not null, the path must start with the provided string.
 *                            E.g. A path like /user/ will match /user/account too
 */
class LocationModel extends Model
{
	
	/**
	 * 
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema) {
		$schema->client   = new \Reference('authapp');
		$schema->default  = new BooleanField();
		$schema->protocol = new StringField(255);
		$schema->hostname = new StringField(255);
		$schema->path     = new StringField(255);
	}

}
