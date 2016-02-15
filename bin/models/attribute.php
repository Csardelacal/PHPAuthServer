<?php

class AttributeModel extends spitfire\Model
{
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		
		$schema->_id      = new StringField( 20);
		
		$schema->name     = new StringField( 50);
		$schema->default  = new StringField(200);
		$schema->required = new BooleanField();
		
		$schema->readable = new EnumField('public', 'groups', 'related', 'me', 'nem');
		$schema->writable = new EnumField('public', 'groups', 'related', 'me', 'nem');
	}

}
