<?php

/**
 * This controller greets the users when they get to the application. If the app
 * was not yet set up, it will redirect the user over to the SetupController
 * 
 */
class HomeController extends Controller
{
	public function index() {
		
		#If the user table has no records, we need to set up the application
		if (db()->table('user')->getAll()->count() < 1) { return $this->response->getHeaders()->redirect(new URL('setup')); }
		
		$s = new session();
		
		#If the user is logged in, we show them their dashboard, otherwise we'll send them away to get logged in.
		if ($s->getUser()) {	$this->view->set('user', db()->table('user')->get('_id', $s->getUser())); } 
		else               { return $this->response->getHeaders()->redirect(new URL('user', 'login')); }
	}
}
