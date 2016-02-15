<?php

class AuthController extends Controller
{
	
	public function index() {
		
	}
	
	public function oauth($token) {
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			
		}
	}
	
	public function xauth() {
		
	}
	
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
			
			return $this->response->getHeaders()->redirect((string)new URL('user', 'dashboard'));
		}
	}
	
}
