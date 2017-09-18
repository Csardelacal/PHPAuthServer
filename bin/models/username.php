<?php

class UsernameModel extends spitfire\Model
{
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		$schema->user    = new Reference('user');
		$schema->name    = new StringField(20);
		$schema->expires = new IntegerField();
	}
	
	public function __toString() {
		return $this->name;
	}

}
