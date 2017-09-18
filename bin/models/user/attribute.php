<?php namespace user;

use spitfire\Model;

class AttributeModel extends Model
{
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		$schema->user     = new \Reference('user');
		$schema->attr     = new \Reference('attribute');
		$schema->value    = new \StringField(200);
		$schema->modified = new \IntegerField();
	}
	
	public function onbeforesave() {
		$this->modified = time();
	}

}
