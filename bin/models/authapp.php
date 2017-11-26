<?php

/**
 * 
 * @todo Add ownership to the apps. So a certain user can administrate his own apps
 */
class AuthAppModel extends spitfire\Model
{
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
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
		
		return $p? (int)$p->state : connection\AuthModel::STATE_PENDING;
	}
	
	public function getContext($context) {
		if (is_array($context) || $context instanceof spitfire\io\Get) {
			return collect($context instanceof spitfire\io\Get? $context->getRaw() : $context)->each(function ($e) { return $this->getContext($e); });
		}
		
		$db = $this->getTable()->getDb();
		$q  = $db->table('connection\context')->getAll();
		
		$q->addRestriction('app', $this);
		$q->addRestriction('ctx', $context);
		$q->group()->addRestriction('expires', time(), '>')->addRestriction('expires', null, 'IS');
		
		$r = $q->fetch();
		
		return $r? new \auth\Context(true, $r->ctx, $r->app->appID, $r->title, $r->descr, $r->expires) :
			new \auth\Context(false, $context, $this->appID, null, null, null);
	}

	public function __toString() {
		return sprintf('App (%s)', $this->name);
	}

}
