<?php namespace authentication;

use Reference;
use BooleanField;
use IntegerField;
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
 * The two factor provider allows a user to set up a two factor mechanism that 
 * will verify their identity in a second step when logging into the application.
 * 
 * @property \UserModel $user The user this authentication provider is registered to
 * @property string $type The type of provider, can be email, phone or password
 * @property \PassportModel $passport Whether this authentication provider also provides a unique identity
 * @property bool $prefered Whether this is the user's preferred authentication method
 * @property int $updated When the provider was last updated
 * @property int $expires Whether and when the provider expires
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class ProviderModel extends Model
{
	
	const TYPE_EMAIL = 'email';
	const TYPE_PHONE = 'phone';
	const TYPE_CODES = 'backup-code';
	const TYPE_TOTP  = 'rfc6238';
	const TYPE_PASSWORD = 'password';
	
	/**
	 * 
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema) {
		/*
		 * The user this contact information belongs to.
		 */
		$schema->user = new Reference('user');
		$schema->type = new StringField(20);
		
		/*
		 * Many auth providers rely on the user being able to receive a communication
		 * from the provider. This also daisychains providers and contacts so if the
		 * user removes a contact that is required for 2FA, the 2FA provider will
		 * be removed too.
		 */
		$schema->passport = new Reference('passport');
		
		/*
		 * The content of a passport is the username / email address /etc that the
		 * user provided in an unaltered fashion.
		 */
		$schema->content = new StringField(150);
		
		/*
		 * Allows the application to resort to the last used mechanism whenever choosing
		 * a default, and to remind a user if a provider is becoming stale.
		 */
		$schema->lastUsed  = new IntegerField(true);
		
		
		$schema->created   = new IntegerField(true);
		$schema->updated   = new IntegerField(true);
		
		/*
		 * When a user requests the removal of a contact, the system will first 
		 * set the expiration flag so a job to permanently delete it can be created
		 * and the contact be reviewed for a while.
		 */
		$schema->expires = new IntegerField(true);
	}
	
	public function onbeforesave(): void {
		parent::onbeforesave();
		
		if (!$this->created) {
			$this->created = time();
		}
		
		$this->updated = time();
	}

}
