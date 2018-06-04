<?php

use auth\Context;
use connection\AuthModel;
use spitfire\io\Get;
use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * 
 * @todo Add ownership to the apps. So a certain user can administrate his own apps
 */
class AuthAppModel extends Model
{
	
	public function definitions(Schema $schema) {
		$schema->appID  = new StringField(20);
		$schema->appSecret = new StringField(50);
		
		$schema->name   = new StringField(20);
		$schema->url    = new StringField(100);
		$schema->icon   = new FileField();
		
		/*
		 * The webhook allows the App developer to provide a URL that will be called
		 * when a user modifies it's data.
		 */
		$schema->webhook= new StringField(100);
		
		$schema->appID->setUnique(true);
	}
	
	public function canAccess($app, $user = null, $context = null) {
		
		if (is_array($context)) {
			return collect($context)->reduce(function ($p, $e) use ($app, $user) {
				return min($p, $this->canAccess($app, $user, $e));
			}, 2);
		}
		
		$db = $this->getTable()->getDb();
		$q  = $db->table('connection\auth')->getAll();
		
		$q->addRestriction('source', $this);
		$q->addRestriction('target', $app);
		
		if ($context) {
			$q->addRestriction('context', $context);
		}
		
		if ($user) {
			$q->addRestriction('user', $user);
		}
		else {
			$q->addRestriction('user', null, 'IS');
		}
		
		$q->group()->addRestriction('expires', null, 'IS')->addRestriction('expires', time(), '>');
		$p = $q->fetch();
		
		return $p? (int)$p->state : ($user? $this->canAccess($app, null, $context) : AuthModel::STATE_PENDING);
	}
	
	public function getContext($context) {
		if (is_array($context) || $context instanceof Get) {
			return collect($context instanceof Get? $context->getRaw() : $context)->each(function ($e) { return $this->getContext($e); });
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
