<?php namespace email;

use AuthAppModel;
use Reference;
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
 * An inbox is a email address that is linked to an application. When PHPAS receives
 * a request indicating that there is an incoming message, the system will lookup
 * the address in this table and set the application linked to it as a recipient.
 *
 * Please note that addresses are not unique in the database and therefore could
 * potentially multiplex incoming messages to multiple apps.
 *
 * @property string $address Full email address including the domain to be routed.
 * @property \AuthAppModel $app The application to be routed to
 *
 * @deprecated since version 0.1-dev
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class InboxModel extends Model
{
	/**
	 *
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema) {
		$schema->address = new StringField(255);
		$schema->app = new Reference(AuthAppModel::class);
		$schema->psk = new StringField(120);
	}

}
