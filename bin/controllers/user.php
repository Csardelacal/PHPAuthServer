<?php

class UserController extends Controller
{
	
	
	
	public function register() {
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$user = db()->table('user')->newRecord();
			$user->email    = $_POST['email'];
			$user->password = $_POST['password'];
			$user->verified = false;
			$user->created  = time();
			$user->store();
			
			$username = db()->table('username')->newRecord();
			$username->user = $user;
			$username->name = $_POST['username'];
			$username->store();
			
			$s = new session();
			$s->lock($user->__id);
			
			return $this->response->getHeaders()->redirect((string)new URL('user', 'dashboard'));
		}
		
		$query = db()->table('attribute')->get('writable', Array('public', 'groups', 'related', 'me'));
		$query->addRestriction('required', true);
		
		$this->view->set('attributes', $query->fetchAll());
	}
	
	public function login() {
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			
			$query = db()->table('user')->getAll();
			
			$query->group()
					  ->addRestriction('email', $_POST['username'])
					  ->addRestriction('usernames', db()->table('username')->get('name', $_POST['username'])->addRestriction('expires', NULL, 'IS'))
					->endGroup();
			
			$user = $query->fetch();
			
			if ($user && $user->checkPassword($_POST['password'])) {
				$session = new session();
				$session->lock($user->_id);
				return $this->response->getHeaders()->redirect(new URL());
			} else {
				$this->view->set('message', 'Username or password did not match');
			}
		}
		
	}
	
	public function detail($userid) {
		
		if ($_GET['token']) { $token = db()->table('token')->get('token', $_GET['token'])->fetch(); }
		else                { $token = null; }
		
		if ($token !== null && $token->expires !== null && $token->expires !== '' && $token->expires < time()) 
			{ throw new \spitfire\exceptions\PublicException('Your token is expired', 401); }
		
		#Get the affected profile
		$profile = db()->table('user')->get('usernames', db()->table('username')->get('name', $userid)->addRestriction('expires', NULL, 'IS'))->fetch();
		if (!$profile) { throw new \spitfire\exceptions\PublicException('No user found', 404); }
		
		#Set the base permissions
		$permissions = Array('public');
		
		#Check if the two users are in the same group
		$groupquery = db()->table('group')->getAll();
		$groupquery->addRestriction('members', db()->table('user\group')->get('user', $profile->getQuery()));
		$groupquery->addRestriction('members', db()->table('user\group')->get('user', $token->user->getQuery()));
		
		$groups = $groupquery->fetchAll();
		if (isset($groups[0])) { $permissions[] = 'group'; }
		
		#Check if the user is himself
		if ($profile->_id === $token->user->_id) { $permissions[] = 'me'; }
		
		#Get the public attributes
		$attributes = db()->table('attribute')->get('readable', $permissions)->fetchAll();
		
		var_dump($permissions);
		var_dump($attributes);
		var_dump(spitfire()->getMessages());
		die();
	}
	
}