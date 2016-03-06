<?php

class AuthController extends Controller
{
	
	public function index() {
		
	}
	
	public function oauth($token) {
		
		$successURL = isset($_GET['success'])? $_GET['success'] : null;
		$failureURL = isset($_GET['failure'])? $_GET['failure'] : null;
		
		$grant      = isset($_GET['grant'])  ? (int)$_GET['grant'] === 1 : null;
		$session    = new session();
		
		if (!$session->getUser()) { return $this->response->getHeaders()->redirect(new URL('user', 'login', Array('returnto' => URL::current()))); }
		if ($grant === true)      { return $this->response->getHeaders()->redirect($successURL); }
		if ($grant === false)     { return $this->response->getHeaders()->redirect($failureURL); }
		
	}
	
	/**
	 * As opposed to the OAuth endpoint, the xauth endpoint allows a machine to
	 * log in a user by using their credentials.
	 * 
	 * The use of tokens is still enforced. Some installations may have this 
	 * endpoint disabled to force OAuth.
	 * 
	 * @param string $token
	 */
	public function xauth($token) {
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			
		}
	}
	
}
