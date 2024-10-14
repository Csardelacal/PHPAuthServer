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
				->addRestriction('members', db()->table(user\GroupModel::class)->get('user', $this->user))->endGroup();
		}
		
		$pages = new \spitfire\storage\database\pagination\Paginator($query);
		
		$this->view->set('pagination', $pages);
		$this->view->set('records',    $pages->records());
		
	}
	
	public function detail($id) {
		//if (!$this->user) { throw new PublicException('Members only', 401); }
		
		$group    = db()->table('group')->get(is_numeric($id)? '_id' : 'name', $id)->fetch();
		$writable = !!$group->members->getQuery()->addRestriction('user', $this->user)->addRestriction('role', Array('admin', 'owner'))->fetch();
		
		if (!$group) {
			throw new PublicException('No group found');
		}
		
		if (!$group->public && !$this->isAdmin && db()->table(user\GroupModel::class)->get('user', $this->user)->addRestriction('group', $group)->fetch()) {
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
	
	public function addUser($groupid = null, $userid = null) {
		
		if ($userid  === null) { $userid  = $_POST['username']; }
		if ($groupid === null) { $groupid = $_POST['group']; }
		
		
		#Get the user attached to that profile
		$profile = db()->table('user')->get('_id', $userid)->fetch()? :
				db()->table('user')->get('usernames', db()->table('username')->get('name', $userid)->
						group()->addRestriction('expires', NULL, 'IS')->addRestriction('expires', time(), '>')->endGroup())->fetch();
		
		$group   = db()->table('group')->get('_id', $groupid)->fetch();
		
		#Find an appropriate role for the user
		//TODO: Validate this data
		$role = isset($_POST['role'])? $_POST['role'] : 'member';
		
		#Check if we have all the data we need to work with
		if (!$profile) { throw new Exception('No user found', 404); }
		if (!$group)   { throw new Exception('No group found', 404); }
		
		
		#Add the user to the group
		$membership = db()->table(user\GroupModel::class)->get('user', $profile)->addRestriction('group', $group)->fetch()? : db()->table(user\GroupModel::class)->newRecord();
		$membership->user  = $profile;
		$membership->group = $group;
		$membership->role  = $role;
		$membership->store();
		
		if ($this->request->getPath()->getFormat() === 'php') {
			return $this->response->setBody('Redirecting...')->getHeaders()->redirect(new URL('group', 'detail', $groupid));
		}
	}
	
	public function removeUser($memberid) {
		
		
		#Fetch the membership and destroy shortly afterwards
		$membership = db()->table(user\GroupModel::class)->get('_id', $memberid)->fetch()? : null;
		
		if ($membership) {
			$group = $membership->group;
			$membership->delete();
		}
		
		if ($this->request->getPath()->getFormat() === 'php') {
			return $this->response->setBody('Redirecting...')->getHeaders()->redirect($group? new URL('group', 'detail', $group->_id) : new URL());
		}
	}
	
}

