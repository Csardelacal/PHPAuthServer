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
 * An access token connects up to three parties in a relationship that authenticates
 * the following:
 * 
 * * A resource owner, who owns the resources on the server, and wishes to grant access to the client. This is generally a human.
 * * A client, an application that wishes to retrieve data or issue commands to the server.
 * * A server. An application that holds the owner's information and wishes to authenticate the client's requests.
 * 
 * These tokens can be of two kinds, access tokens or refresh tokens. When a "public"
 * application issues an access token, no refresh token is generated. A private 
 * application may request a refresh token to be issued.
 * 
 * Depending on which fields are populated, the token may be used for different
 * scenarios. 
 * 
 * * A token may have no owner, which means that the owner is implied to be the server.
 * * A token may have no client nor owner, making it a client-credential so that an application can rate limit clients
 * * If the client and server are the same app, the token is a session token and used to log the user into the application.
 * 
 * 
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
class TokenModel extends Model
{
	
	const TOKEN_PREFIX = 't_';
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
	public static function create(SessionModel $session, $client, $server = null, $owner = null, $expires = 14400) {
		$record = db()->table('access\token')->newRecord();
		$record->created = time();
		$record->owner   = $owner;
		$record->host    = $server?: $client;
		$record->client  = $client;
		$record->session = $session;
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