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
		$token = md5(uniqid(mt_rand(), true));
		
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