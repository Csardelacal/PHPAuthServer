<?php namespace token;

use AuthAppModel;
use IntegerField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;
use TokenModel;

/**
 * 
 * @deprecated since version 0.1-dev
 */
class UsageModel extends Model
{
	
	/**
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		$schema->token   = new Reference(TokenModel::class);
		$schema->app     = new Reference(AuthAppModel::class);
		$schema->created = new IntegerField(true);
		
		$schema->index($schema->token, $schema->app);
	}
	
	public function onbeforesave() {
		if (!$this->created) { $this->created = time(); }
	}

}