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
		
		#Check if the two users are in the same group
		$groupquery = db()->table('group')->getAll();
		$groupquery->addRestriction('members', db()->table('user\group')->get('user__id', db()->table('user')->get('_id', $userid)));
		$groupquery->addRestriction('members', db()->table('user\group')->get('user', $token->user->getQuery()));
		
		$groups = $groupquery->fetch();
		
		#Get the public attributes
		$attributes = db()->table('attribute')->get('readable', 'public')->fetchAll();
		
		print_r(spitfire()->getMessages());
		die();
	}
	
}