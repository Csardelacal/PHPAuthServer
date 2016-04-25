<?php

use spitfire\exceptions\PublicException;

class EditController extends BaseController
{
	
	public function username() {
		if (!$this->user) { throw new PublicException('You need to be logged in', 401); }
		
		if ($this->request->isPost()) {
			#Check if the suername was sent at all
			if (!isset($_POST['username'])) { throw new PublicException('Invalid request', 400); }
			
			#Once we know it's here, check if it's valid
			$username = $_POST['username'];
			if (!preg_match('/^[a-zA-z][a-zA-z0-9\-\_]{2,19}$/', $username)) { throw new PublicException('Invalid username', 400); }
			
			#Check if the new username is taken
			$dupquery = db()->table('username')->get('name', $username)->addRestriction('user__id', $this->user->_id, '<>')
					->group()
						->addRestriction('expires', null, 'IS')
						->addRestriction('expires', time(), '>')
					->endGroup();
			
			if ($dupquery->count() !== 0) { throw new PublicException('Username is taken', 400); }
			
			#Go on, now setting the old username as past
			$old = $this->user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch();
			$new = db()->table('username')->newRecord();
			
			$old->expires = time() + (90 * 24 * 3600);
			$old->store();
			
			$new->user = $this->user;
			$new->name = $username;
			$new->expires = null;
			$new->store();
		}
	}
	
}