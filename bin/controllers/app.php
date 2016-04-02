<?php

class AppController extends Controller
{
	
	private $session;
	private $user;
	
	public function _onload() {
		$this->session = new session();
		
		#Get the user model
		$this->user = $this->session->getUser()? db()->table('user')->get('_id', $this->session->getUser())->fetch() : null;
		if (!$this->user) { throw new spitfire\exceptions\PublicException('Not logged in', 403); }
		
		#Check if he's an admin
		$admingroupid = SysSettingModel::getValue('admin.group');
		$isAdmin      = !!db()->table('user\group')->get('group__id', $admingroupid)->addRestriction('user', $this->user)->fetch();
		if (!$isAdmin) { throw new spitfire\exceptions\PublicException('Not an admin', 401); }
		
	}
	
	public function index() {
		
		$query = db()->table('authapp')->getAll();
		$pag   = new Pagination($query, 'app');
		
		$this->view->set('query',      $query);
		$this->view->set('pagination', $pag);
		
	}
	
}
