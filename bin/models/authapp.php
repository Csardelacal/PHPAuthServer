<?php

use auth\Context;
use connection\AuthModel;
use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * 
@property UserModel $owner The user that created the client and manages it
 * @todo Add ownership to the apps. So a certain user can administrate his own apps
 */
class AuthAppModel extends Model
{
	
	public function definitions(Schema $schema) {
		$schema->appID  = new StringField(20);
		$schema->appID->setUnique(true);
		
		/**
		 * @deprecated since version 0.1-dev
		 */
		$schema->appSecret = new StringField(50);
		
		$schema->owner  = new Reference(UserModel::class);
		
		$schema->name   = new StringField(20);
		$schema->icon   = new Reference(IconModel::class);
		
		/*
		 * Whether the application requires two factor authentication to log into it.
		 * If that's the case, PHPAS will require the user to  reauthenticate before 
		 * logging into it.
		 */
		$schema->twofactor = new BooleanField();
		$schema->twofactor->setNullable(false);
		
		$schema->credentials = new ChildrenField(client\CredentialModel::class, 'client');
		
	}
	
	public function canAccess($app, $user, $context) {
		
		$db = $this->getTable()->getDb();
		$q  = $db->table('connection\auth')->getAll();
		
		$q->addRestriction('source', $this);
		$q->addRestriction('target', $app);
		$q->addRestriction('context', $context);
		
		if ($user) {
			$q->addRestriction('user', $user);
		}
		else {
			$q->addRestriction('user', null, 'IS');
		}
		
		$q->group()->addRestriction('expires', null, 'IS')->addRestriction('expires', time(), '>');
		$result = $q->all();
		
		$_r = $result->reduce(function (AuthModel$c, AuthModel$e) {
			if ($e->user && $e->final) { return $e; }
			if ($c->user && $c->final) { return $c; }
			if ($e->user && $e->state == AuthModel::STATE_DENIED) { return $e; }
			if ($c->user && $c->state == AuthModel::STATE_DENIED) { return $c; }
			if ($e->final) { return $e; }
			if ($c->final) { return $c; }
			if ($e->user ) { return $e; }
			if ($c->user ) { return $c; }
			return $e;
		}, $result->rewind());
			
		return $_r? $_r->state : AuthModel::STATE_PENDING;
	}
	
	public function getContext($context) {
		if (!is_string($context)) {
			throw new InvalidArgumentException('Context must be string', 1806130942);
		}
		
		$db = $this->getTable()->getDb();
		$q  = $db->table('connection\context')->getAll();
		
		$q->addRestriction('app', $this);
		$q->addRestriction('ctx', $context);
		$q->group()->addRestriction('expires', time(), '>')->addRestriction('expires', null, 'IS');
		
		$r = $q->fetch();
		
		return $r? new Context(true, $r->ctx, $r->app->appID, $r->title, $r->descr, $r->expires) :
			new Context(false, $context, $this->appID, null, null, null);
	}

	public function __toString() {
		return sprintf('App (%s)', $this->name);
	}

}
