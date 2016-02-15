<?php

class AuthAppModel extends spitfire\Model
{
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		$schema->appID  = new StringField(20);
		$schema->appSec = new StringField(50);
		
		$schema->name   = new StringField(20);
		$schema->icon   = new FileField();
		
		$schema->appID->setUnique(true);
	}

}