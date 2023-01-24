<?php

use spitfire\Model;
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
 * A session connects a user with a device from which they logged in, when the user
 * logs in, a session is started for them. When a user ends a session, all related
 * tokens that have not been granted as offline will be terminated.
 *
 * @property string        $_id      Session ID
 * @property UserModel     $user     Session owner
 * @property LocationModel $location The location from where the session was authorized
 * @property DeviceModel   $device   The device from which the session was authorized
 *
 * @property int $created The timestamp of creation
 * @property int $expires The timestamp this record expires
 *
 * @todo Introduce a lifecycle property that tells whether the session is active, winding-down
 * or finished. These should be set by the SessionEndTask to indiciate it's progress notifying
 * other applications of the session termination. Usually a session should not involve a lot of
 * active tokens so they should be quick to expire and clean.
 *
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class SessionModel extends Model
{
	
	const TOKEN_PREFIX = 's_';
	const TOKEN_LENGTH = 50;
	
	/**
	 *
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema)
	{
		#The session ID will be retrieved from the Session
		unset($schema->_id);
		$schema->_id      = new StringField(self::TOKEN_LENGTH);
		$schema->user     = new Reference(UserModel::class);
		
		/*
		 * Applications can use the IP address of the device to prevent an attacker
		 * generating a token from a certain IP address and sending it to an unsuspecting
		 * victim that may authorize this token from a different IP address.
		 */
		$schema->ip       = new StringField(128);
		
		/**
		 * Location information for the session. Where did the user connect from.
		 */
		$schema->country  = new StringField(02);
		$schema->city     = new StringField(20);
		
		$schema->created  = new IntegerField(true);
		$schema->expires  = new IntegerField(true);
		
		$schema->index($schema->_id)->setPrimary(true);
		$schema->index($schema->expires);
	}
	
	public function onbeforesave(): void
	{
		parent::onbeforesave();
		
		if (!$this->_id) {
			do {
				$this->_id = substr(self::TOKEN_PREFIX . bin2hex(random_bytes(25)), 0, self::TOKEN_LENGTH);
			}
			while (db()->table('session')->get('_id', $this->_id)->first());
		}
		
		if (!$this->created) {
			$this->created = time();
		}
		if (!$this->expires) {
			$this->expires = time() + 86400 * 365;
		}
	}
}
