<?php namespace connection;

use AuthAppModel;
use BooleanField;
use IntegerField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use UserModel;

/**
 * This model manages the connections between applications. Please note that, if
 * the user field is set, this applies only to that user. Generics are marked with
 * null.
 * 
 * @property AuthAppModel $source  Application granting access to it's API
 * @property AuthAppModel $target  Application reading information from the API
 * @property UserModel    $user    User granting / denying access to the information
 * @property int           $state   Flag to indicate the status of the exchange request
 * @property int           $created Timestamp of the creation of this record
 * @property int           $expires The record may expire, this will contain that information
 * 
 * @deprecated since version 0.1-dev
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class AuthModel extends Model
{
	
	/**
	 * These states allow the application to represent with simple integers the
	 * status of the exchange requests.
	 * 
	 * Please note that these are not sorted in any meaningful fashion and should
	 * therefore not be used with comparisons like ">" or "<".
	 */
	const STATE_PENDING    = 0;
	const STATE_DENIED     = 1;
	const STATE_AUTHORIZED = 2;
	
	/**
	 * 
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema $schema) {
		$schema->source  = new Reference('authapp');
		$schema->target  = new Reference('authapp');
		$schema->user    = new Reference('user');
		$schema->state   = new IntegerField(true);
		$schema->context = new StringField(50);
		$schema->created = new IntegerField(true);
		$schema->expires = new IntegerField(true);
		$schema->final   = new BooleanField();
	}
	
	public function onbeforesave() {
		if (!$this->created) { $this->created = time(); }
	}

}