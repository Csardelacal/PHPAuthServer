<?php namespace attribute;

use Reference;
use StringField;
use TextField;
use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * A validator model can reference a validator that the system provides for the
 * user to choose and configure. The validators we need to provide need not only
 * validate the data but also inform the system of what kind of data they're good
 * for.
 */
class ValidatorModel extends Model
{
	public function definitions(Schema $schema) {
		$schema->attribute = new Reference('attribute');
		$schema->validator = new StringField(100);
		$schema->settings  = new TextField();
	}

}

