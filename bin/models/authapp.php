<?php

use auth\Context;
use connection\AuthModel;
use spitfire\collection\Collection;
use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 *
 * @todo Add ownership to the apps. So a certain user can administrate his own apps
 *
 * @property string $appID
 * @property string $appSecret
 */
class AuthAppModel extends Model
{
	
	public function definitions(Schema $schema)
	{
		$schema->appID  = new StringField(20);
		$schema->appSecret = new StringField(50);
		
		$schema->name   = new StringField(20);
		$schema->url    = new StringField(100);
		$schema->icon   = new FileField();
		
		/**
		 * The URL to invoke when the user wishes to logout. This will notify the
		 * client in order to terminate the appropriate session. Please note that
		 * the client needs a mechanism to find the session associated with a token
		 * so it can invalidate it.
		 *
		 * The server will send an empty request, identified by the token it wishes
		 * to expire.
		 */
		$schema->logout = new StringField(1024);
		
		/*
		 * System applications do not need to request permissions to access data,
		 * nor will the user be able to block them. In return, sys apps are only
		 * able to create tokens for administrative users.
		 */
		$schema->system = new BooleanField();
		$schema->system->setNullable(false);
		
		/*
		 * Indicates whether this application should be placed in the app drawer.
		 * This allows applications (including PHPAS) to quickly render navigation
		 * to provide users with the option to jump between apps.
		 */
		$schema->drawer = new BooleanField();
		$schema->drawer->setNullable(false);
		
		$schema->appID->setUnique(true);
	}
	
	public function canAccess($app, $user, $context)
	{
		
		$db = $this->getTable()->getDb();
		$q  = $db->table(connection\AuthModel::class)->getAll();
		
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
			if ($e->user && $e->final) {
				return $e;
			}
			if ($c->user && $c->final) {
				return $c;
			}
			if ($e->user && $e->state == AuthModel::STATE_DENIED) {
				return $e;
			}
			if ($c->user && $c->state == AuthModel::STATE_DENIED) {
				return $c;
			}
			if ($e->final) {
				return $e;
			}
			if ($c->final) {
				return $c;
			}
			if ($e->user) {
				return $e;
			}
			if ($c->user) {
				return $c;
			}
			return $e;
		}, $result->first());
		
		return $_r? $_r->state : AuthModel::STATE_PENDING;
	}
	
	public function getContext($context)
	{
		if (!is_string($context)) {
			throw new InvalidArgumentException('Context must be string', 1806130942);
		}
		
		$db = $this->getTable()->getDb();
		$q  = $db->table(connection\ContextModel::class)->getAll();
		
		$q->addRestriction('app', $this);
		$q->addRestriction('ctx', $context);
		$q->group()->addRestriction('expires', time(), '>')->addRestriction('expires', null, 'IS');
		
		$r = $q->fetch();
		
		return $r? new Context(true, $r->ctx, $r->app->appID, $r->title, $r->descr, $r->expires) :
		new Context(false, $context, $this->appID, null, null, null);
	}
	
	public function __toString()
	{
		return sprintf('App (%s)', $this->name);
	}
}
