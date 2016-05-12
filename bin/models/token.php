<?php

class TokenModel extends spitfire\Model
{
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		$schema->token   = new StringField(50);
		
		$schema->user    = new Reference('user');
		$schema->app     = new Reference('authapp');
		
		$schema->expires = new IntegerField();
		$schema->extends = new BooleanField();
		
		$schema->token->setUnique(true);
		
	}
	
	public static function create($app, $expires = 14400, $extends = true) {
		$token = substr(str_replace(Array('&', '=', '+'), '', base64_encode(openssl_random_pseudo_bytes(45, $secure))), 0, 50);
		
		if (!$secure) { throw new spitfire\exceptions\PrivateException('Could not generate secure token', 403); }
		
		$record = db()->table('token')->newRecord();
		$record->token   = $token;
		$record->user    = null;
		$record->app     = $app;
		$record->expires = time() + $expires;
		$record->extends = $extends;
		$record->store();
		
		return $record;
	}

}