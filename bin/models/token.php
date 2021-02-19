<?php

use spitfire\exceptions\PrivateException;
use spitfire\Model;
use spitfire\storage\database\Schema;

class TokenModel extends Model
{
	
	public function definitions(Schema $schema) {
		$schema->token   = new StringField(50);
		
		$schema->user    = new Reference('user');
		$schema->app     = new Reference('authapp');
		
		$schema->created = new IntegerField();
		$schema->expires = new IntegerField();
		$schema->ttl     = new IntegerField();
		
		$schema->session = new Reference('session');
		
		/*
		 * Applications can use the IP address of the device to prevent an attacker
		 * generating a token from a certain IP address and sending it to an unsuspecting
		 * victim that may authorize this token from a different IP address.
		 */
		$schema->ip       = new StringField(128);
		
		$schema->token->setUnique(true);
		
	}
	
	public static function create($app, $user, $expires = 14400) {
		$token = substr(bin2hex(openssl_random_pseudo_bytes(25, $secure)), 0, 50);
		
		if (!$secure) { throw new PrivateException('Could not generate secure token', 403); }
		if (db()->table('token')->get('token', $token)->fetch()) { return self::create($app, $user, $expires); }
		
		$record = db()->table('token')->newRecord();
		$record->token   = $token;
		$record->created = time();
		$record->user    = $user;
		$record->app     = $app;
		$record->expires = time() + $expires;
		$record->ttl     = $expires;
		$record->store();
		return $record;
	}

}