<?php namespace user;

use spitfire\Model;

class AttributeModel extends Model
{
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		$schema->user  = new \Reference('user');
		$schema->attr  = new \Reference('attribute');
		$schema->value = new \StringField(200);
	}

}
