<?php namespace user;

use IntegerField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;

class AuthorizedappModel extends Model
{
	
	public function definitions(Schema $schema) {
		$schema->user    = new Reference('user');
		$schema->app     = new Reference('authapp');
		$schema->created = new IntegerField(true);
		$schema->revoked = new IntegerField(true);
	}
	
	public function onbeforesave() {
		if (!$this->created) { $this->created = time(); }
	}

}
