<?php

class UserModel extends spitfire\Model
{
	
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		$schema->password = new StringField(50);
		$schema->email    = new StringField(30);
		
		$schema->verified = new BooleanField();
		$schema->created  = new IntegerField();
		$schema->picture  = new FileField();
		
		$schema->email->setUnique(true);
		
	}

}
