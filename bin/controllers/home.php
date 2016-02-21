<?php

/**
 * Prebuilt test controller. Use this to test all the components built into
 * for right operation. This should be deleted whe using Spitfire.
 */

class HomeController extends Controller
{
	public function index() {
		
		#If the user table has no records, we need to set up the application
		if (db()->table('user')->getAll()->count() < 1) { return $this->response->getHeaders()->redirect(new URL('setup')); }
		
		$s = new session();
		
		#If the user is logged in, we show them their dashboard, otherwise we'll send them away.
		if ($s->getUser()) {	$this->view->set('user', db()->table('user')->get('_id', $s->getUser())); } 
		else               { $this->view->set('user', null); }
	}
}