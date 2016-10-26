<?php

use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;

class GroupController extends BaseController
{
	
	public function index() {
		
		//if (!$this->user) { throw new PublicException('Only registered users', 401); }
		
		if ($this->isAdmin) { $query = db()->table('group')->getAll(); }
		else {
			$query = db()->table('group')->getAll()->group()
				->addRestriction('public', 1)
				->addRestriction('members', db()->table('user\group')->get('user', $this->user))->endGroup();
		}
		
		$paginator = new Pagination($query);
		
		$this->view->set('records',    $query->fetchAll());
		$this->view->set('pagination', $paginator);
		
	}
	
	public function detail($id) {
		//if (!$this->user) { throw new PublicException('Members only', 401); }
		
		$group    = db()->table('group')->get('_id', $id)->fetch();
		$writable = !!$group->members->getQuery()->addRestriction('user', $this->user)->addRestriction('role', Array('admin', 'owner'))->fetch();
		
		if (!$group) {
			throw new PublicException('No group found');
		}
		
		if (!$group->public && !$this->isAdmin && db()->table('user\group')->get('user', $this->user)->addRestriction('group', $group)->fetch()) {
			throw new PublicException('No group found');
		}
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not posted'); }
			if (!$writable)                { throw new PublicException('Not permitted', 401); }
			
			$group->name        = $_POST['name'];
			$group->description = $_POST['description'];
			
			$group->store();
			
		} catch (HTTPMethodException$ex) { /*Do nothing*/ }
		
		$this->view->set('group', $group);
		$this->view->set('members', $group->members->getQuery()->fetchAll()); //Converts the adapter to array
		$this->view->set('editable', $writable);
	}
	
}

