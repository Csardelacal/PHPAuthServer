<?php namespace access;

use AuthAppModel;
use IntegerField;
use Reference;
use SessionModel;
use spitfire\exceptions\PrivateException;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use UserModel;
use function db;

/**
 * While structurally extremely similar to regular access tokens, the refresh token
 * has no ability to provide direct access to a resource, instead it needs to be 
 * traded for an access token.
 * 
 * Since a refresh token must never be used in a context where the regular access
 * token is accepted, and vice-versa, the system is way more stable whenever we 
 * use two separate models, making it easier for the DBMS and providing powerful
 * isolation between the access and refresh tokens.
 * 
 * @property string $type Either access or refresh
 * @property string $token The token identifier
 * 
 * @property UserModel $owner The resource owner
 * @property AuthAppModel $client The application requesting access to the owner's information
 * @property AuthAppModel $server The application containing the application owner's information
 * @property string $scopes A comma separated list of contexts the client wishes to have access to
 * 
 * @property int $created The time the token was created
 * @property int $expires The time the token is no longer valid
 * @property int $ttl The amount of seconds this token was set to be valid
 * 
 * @property SessionModel $session The session that spawned this token
 * 
 * @todo Make an array adapter for contexts so they get automatically separated
 */
class RefreshModel extends Model
{
	
	const TOKEN_PREFIX = 'r_';
	const TOKEN_LENGTH = 50;
	
	public function definitions(Schema $schema) {
		$schema->token   = new StringField(self::TOKEN_LENGTH);
		
		$schema->owner   = new Reference('user');
		$schema->client  = new Reference('authapp');
		$schema->host    = new Reference('authapp');
		
		$schema->scopes  = new StringField(255);
		
		$schema->created = new IntegerField(true);
		$schema->expires = new IntegerField(true);
		$schema->ttl     = new IntegerField(true);
		
		$schema->session = new Reference(SessionModel::class);
		
		$schema->token->setUnique(true);
		
	}
	
	/**
	 * There's only one endpoint generating tokens, and the function's signature is 
	 * getting very unwieldy. So we're moving best this to the controller.
	 * 
	 * @deprecated since version 0.2-dev
	 * @param SessionModel $session
	 * @param type $client
	 * @param type $server
	 * @param type $owner
	 * @param type $expires
	 * @return type
	 * @throws PrivateException
	 */
	public static function create($client, $server = null, $owner = null, $expires = 63072000) {
		$record = db()->table('access\refresh')->newRecord();
		$record->created = time();
		$record->owner   = $owner;
		$record->host    = $server?: null;
		$record->client  = $client;
		$record->expires = time() + $expires;
		$record->ttl     = $expires;
		$record->store();
		return $record;
	}
	
	public function onbeforesave(): void {
		parent::onbeforesave();
		
		/*
		 * If the token happened to be new, and therefore had no token-id assigned,
		 * we generate a new, unique token identifier for this one.
		 */
		if (!$this->token) {
			do { $this->token = substr(self::TOKEN_PREFIX . bin2hex(random_bytes(25)), 0, self::TOKEN_LENGTH); } 
			while (db()->table('access\token')->get('token', $this->token)->first());
		}
	}

}