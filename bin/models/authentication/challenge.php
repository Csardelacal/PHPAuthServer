<?php namespace authentication;

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

class ChallengeModel extends Model
{
	
	const SECRET_LENGTH = 6;
	const SECRET_EXPIRES = 900;
	
	/**
	 * 
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema) {
		$schema->provider = new \Reference(ProviderModel::class);
		$schema->secret   = new \StringField(64);
		$schema->expires  = new \IntegerField(true);
		$schema->created  = new \IntegerField(true);
		
		$schema->session  = new \Reference('session');
		$schema->cleared  = new \IntegerField(true);
		
		$schema->index($schema->session);
	}
	
	public static function make(ProviderModel$provider, \SessionModel$session = null) {
		$record = db()->table('authentication\challenge')->newRecord();
		$record->provider = $provider;
		$record->secret   = substr(dechex(mt_rand()), 0, 6);
		$record->created  = time();
		$record->session  = $session? : db()->table('session')->get('_id', \spitfire\io\session\Session::getInstance()->sessionId())->first();
		$record->expires  = time() + self::SECRET_EXPIRES;
		$record->store();
		
		return $record;
	}

}
