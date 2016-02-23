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
			
			var_dump($query->fetch());
			print_r(spitfire()->getMessages());
			die();
		}
		
	}
	
}