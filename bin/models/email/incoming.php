<?php namespace email;

use AuthAppModel;
use IntegerField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use TextField;

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
 * Incoming emails. These are sent to applications in PHPAS to be used to provide
 * their service. This is not an email relay to other users and the system will
 * only accept emails addressed to applications.
 *
 * @property string $from The email / user id / guest alias for the user
 * @property InboxModel $to The application being addressed by this message
 * @property OutgoingModel $irt The email the user was responding to
 * @property string $body Body of the message
 *
 * @todo HTML Bodies
 * @todo Attachments
 *
 * @deprecated since version 0.1-dev
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class IncomingModel extends Model
{
	
	/**
	 *
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema) {
		$schema->from = new StringField(255);
		$schema->to   = new Reference(InboxModel::class);
		$schema->irt  = new Reference(OutgoingModel::class);
		$schema->body = new TextField();
		
		$schema->created = new IntegerField(true);
	}

}
