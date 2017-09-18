<?php namespace user;

use BooleanField;
use IntegerField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;
use TextField;

/**
 * Suspensions can be issued to users, allowing to restrict their ability to 
 * perform certain tasks on the application or from logging into the application
 * at all.
 */
class SuspensionModel extends Model
{
	
	public function definitions(Schema $schema) {
		$schema->user = new Reference('user');
		$schema->expires = new IntegerField(true);
		$schema->reason = new TextField();
		$schema->notes = new TextField();
		$schema->preventLogin = new BooleanField();
	}

}