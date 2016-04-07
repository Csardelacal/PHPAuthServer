<?php

/**
 * This controller greets the users when they get to the application. If the app
 * was not yet set up, it will redirect the user over to the SetupController
 * 
 */
class HomeController extends BaseController
{
	public function index() {
		
		#If the user table has no records, we need to set up the application
		if (db()->table('user')->getAll()->count() < 1) { return $this->response->getHeaders()->redirect(new URL('setup')); }
		
		#If the user is logged in, we show them their dashboard, otherwise we'll send them away to get logged in.
		if ($this->user !== null) { $this->view->set('user', $this->user); } 
		else                      { return $this->response->getHeaders()->redirect(new URL('user', 'login')); }
	}
}
