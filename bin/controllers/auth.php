<?php

class AuthController extends Controller
{
	
	public function index() {
		
	}
	
	public function oauth($token) {
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			
		}
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
