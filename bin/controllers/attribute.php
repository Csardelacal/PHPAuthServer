<?php

class AttributeController extends BaseController
{
	
	public function _onload() {
		parent::_onload();
		
		if (!$this->isAdmin) { 
			throw new \spitfire\exceptions\PrivateException('You cannot acces this section without being an admin', 403); 
		}
	}
	
	public function index() {
		
		$query = db()->table('attribute')->getAll();
		
		$this->view->set('pagination', new Pagination($query));
		$this->view->set('attributes', $query->fetchAll());
		
	}
	
	public function create() {
		
		$bean = db()->table('attribute')->getBean();
		
		try {
			$bean->validate();
			$bean->readPost();
			$bean->setDBRecord(db()->table('attribute')->newRecord());
			$bean->updateDBRecord()->store();
			
			$this->response->getHeaders()->redirect(new URL('attribute', Array('message' => 'created')));
		} catch (\spitfire\validation\ValidationException$e) {
			$this->view->set('messages', $e->getResult());
		} catch (\spitfire\io\beans\UnSubmittedException$e) {
			//Do nothing
		}
		
		$this->view->set('bean', $bean);
	}
	
	public function edit($id) {
		
		$record = db()->table('attribute')->getTable()->getById($id);
		
		$bean = db()->table('attribute')->getTable()->getBean();
		$bean->setDBRecord($record);
		
		if (!$record) { throw new \spitfire\exceptions\PublicException('Not found', 404); }
		
		try {
			$bean->validate();
			$bean->readPost();
			$bean->updateDBRecord()->store();
			
			$this->response->getHeaders()->redirect(new URL('attribute', Array('message' => 'created')));
		} catch (\spitfire\validation\ValidationException$e) {
			$this->view->set('messages', $e->getResult());
		} catch (\spitfire\io\beans\UnSubmittedException$e) {
			//Do nothing
		}
		
		$this->view->set('bean', $bean);
	}
}
