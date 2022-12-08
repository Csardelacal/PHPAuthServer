<?php namespace user;

use spitfire\Model;
use spitfire\storage\database\Schema;

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
 * The consent model allows applications to record whether a user consented to a
 * certain application / client to access a certain scope within another application.
 *
 * This model refers specifically to user consent. Using permission server, an administrator
 * can override the consent in this file to make sure that certain policy is satisfied.
 *
 * This creates a 3 way connection between a user, an application and a scope within
 * another application so the client requesting access is granted access to the
 * user's resources that match the scope within the resource provider application.
 *
 * @todo Introduce mechanism for users to manage consent.
 * @todo Introduce mechanism for application owners to manage consent
 * @todo Introduce mechanism for administrators to manage consent policy
 *
 * @property \UserModel $user The user granting access
 * @property \AuthAppModel $client The application receiving access
 * @property \client\ScopeModel $scope The scope being granted access to
 * @property int $created When the consent was given
 * @property int $revoked When the consent was revoked
 *
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class ConsentModel extends Model
{
	
	/**
	 *
	 * @param Schema $schema
	 * @return void
	 */
	public function definitions(Schema $schema)
	{
		
		/**
		 * The user granting access.
		 */
		$schema->user = new \Reference(\UserModel::class);
		
		/**
		 * The client that IS GRANTED access to the scope. This means that this client
		 * will be granted access to the scope for this user.
		 */
		$schema->client  = new \Reference(\AuthAppModel::class);
		
		/**
		 * The scope (and therefore the client, this consent GRANTS ACCESS TO.
		 */
		$schema->scope   = new \StringField(50);
		
		/**
		 * Records when the consent was created.
		 */
		$schema->created = new \IntegerField(true);
		
		/**
		 * Records if and when the consent to use this scope was revoked.
		 */
		$schema->revoked = new \IntegerField(true);
	}
	
	public function onbeforesave(): void
	{
		parent::onbeforesave();
		
		/*
		 * Check if the timestamp of creation is provided, and if not, default to
		 * the current time.
		 */
		if (!$this->created) {
			$this->created = time();
		}
	}
}
