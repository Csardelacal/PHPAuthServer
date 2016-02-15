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

}