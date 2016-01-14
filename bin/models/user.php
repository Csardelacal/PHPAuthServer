<?php

class UserModel extends spitfire\Model
{
	
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		$schema->username = new StringField(20);
		$schema->password = new StringField(50);
		$schema->email    = new StringField(30);
		
		$schema->verified = new BooleanField();
	}

}