<?php

use spitfire\exceptions\PublicException;

/**
 * The email controller is one of the weirder controllers in PHPAuthServer. This
 * is due to the fact that the system is not intended to deliver email and it only
 * provides this service to ensure that it completely obfuscates the user's email
 * from the apps which use it to log in and authenticate users.
 */
class EmailController extends BaseController
{
	
	/**
	 * Administrators are allowed to see how the current email queue looks and to
	 * check how many emails were sent recently.
	 * 
	 * @throws PublicException
	 */
	public function index() {
		if (!$this->isAdmin) { throw new PublicException('Not authorized', 401); }
		
		if (isset($_GET['history'])) {
			$queue = db()->table('email')->get('scheduled', time(), '<')->addRestriction('delivered', null, 'IS NOT');
			$queue->setOrder('scheduled', 'ASC');
		} else {
			$queue = db()->table('email')->get('scheduled', time(), '<')->addRestriction('delivered', null, 'IS');
			$queue->setOrder('scheduled', 'DESC');
		}
		
		$this->view->set('pagination', new Pagination($queue));
		$this->view->set('records', $queue->fetchAll());
	}
	
}
