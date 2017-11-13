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
		$db = $this->getTable()->getDb();
		$q  = $db->table('connection\context')->getAll();
		
		$q->addRestriction('app', $this);
		$q->addRestriction('ctx', $context);
		$q->group()->addRestriction('expires', time(), '>')->addRestriction('expires', null, 'IS');
		
		return $q->fetch();
	}

	public function __toString() {
		return sprintf('App (%s)', $this->name);
	}

}
