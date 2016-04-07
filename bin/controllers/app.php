<?php

class AppController extends BaseController
{
		
	public function _onload() {
		parent::_onload();
		
		#Get the user model
		if (!$this->user) { throw new spitfire\exceptions\PublicException('Not logged in', 403); }
		
		#Check if he's an admin
		if (!$this->isAdmin) { throw new spitfire\exceptions\PublicException('Not an admin', 401); }
		
	}
	
	public function index() {
		
		$query = db()->table('authapp')->getAll();
		$pag   = new Pagination($query, 'app');
		
		$this->view->set('query',      $query);
		$this->view->set('pagination', $pag);
		
	}
	
}
