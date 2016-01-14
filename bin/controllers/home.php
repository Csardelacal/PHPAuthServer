<?php

/**
 * Prebuilt test controller. Use this to test all the components built into
 * for right operation. This should be deleted whe using Spitfire.
 */

class homeController extends Controller
{
	public function index() {
		$users = db()->table('user')->getAll()->fetchAll();
		$this->view->set('users', $users);
		$this->view->set('message', 'Hi! I\'m spitfire');
	}
}