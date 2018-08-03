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
		$pagination = new \spitfire\storage\database\pagination\Paginator($query);
		
		$this->view->set('pagination', $pagination);
		$this->view->set('attributes', $pagination->records());
		
	}
	
	public function create() {
		
		$bean = db()->table('attribute')->getBean();
		
		try {
			$bean->validate();
			$bean->readPost();
			$bean->setDBRecord(db()->table('attribute')->newRecord());
			$bean->updateDBRecord()->store();
			
			$this->response->getHeaders()->redirect(url('attribute', Array('message' => 'created')));
		} catch (\spitfire\validation\ValidationException$e) {
			$this->view->set('messages', $e->getResult());
		} catch (\spitfire\io\beans\UnSubmittedException$e) {
			//Do nothing
		}
		
		$this->view->set('bean', $bean);
	}
	
	public function edit($id) {
		
		$record = db()->table('attribute')->getById($id);
		$collector  = new attribute\AttributeValidatorCollector();
		
		
		$bean = db()->table('attribute')->getBean();
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
		$this->view->set('validatorcollector', $collector);
	}
}
