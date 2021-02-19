<?php namespace connection;

use IntegerField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;

/**
 * This model caches contexts, since the context information and the authorization
 * do come from different applications, the server will hold this information for
 * a given time to ensure that the information hasn't been tampered with by the 
 * authorizing app.
 * 
 * @see \access\ScopeModel
 * @deprecated since version 0.1-dev
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class ContextModel extends Model
{
	
	/**
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		$schema->app     = new Reference('authapp');
		$schema->ctx     = new StringField(50);
		$schema->title   = new StringField(50);
		$schema->descr   = new StringField(300);
		$schema->expires = new IntegerField(true);
	}

}