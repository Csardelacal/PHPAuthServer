<?php

use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * An attribute introduces the user's ability to create data and settings in the
 * system. The user can then customize these settings on the account server and
 * see them reflected across several sites.
 * 
 * @todo I'm currently debating how to handle the different defaults for the 
 * different types. I don't know what the best way of handling default files 
 * (for example) would be.
 * 
 * There's the possibility of altering the bean's form with JS when the user selects
 * file as the desired data type which is the only one to require a different input.
 * 
 * @deprecated since version 0.1-dev
 */
class AttributeModel extends Model
{
	
	public function definitions(Schema $schema) {
		
		$schema->_id      = new StringField( 20);
		$schema->_id->setPrimary(true);
		$schema->index($schema->_id)->setPrimary(true);
		
		$schema->name     = new StringField( 50);
		$schema->default  = new StringField(200);
		$schema->required = new BooleanField();
		
		$schema->datatype = new EnumField('string', 'text', 'int', 'boolean', 'file');
		$schema->validate = new ChildrenField('attribute\validator', 'attribute');
		
		$schema->readable = new EnumField('public', 'me', 'nem');
		$schema->writable = new EnumField('public', 'me', 'nem');
	}

}
