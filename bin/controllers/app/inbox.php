<?php namespace app;

use BaseController;
use spitfire\exceptions\HTTPMethodException;

/**
 * 
 * @deprecated since version 0.1-dev
 */
class InboxController extends BaseController
{
	
	public function index() {
		$this->view->set('inboxes', db()->table('email\inbox')->getAll()->all());
	}
	
	public function create() {
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not Posted'); }
			
			$inbox = db()->table('email\inbox')->newRecord();
			$inbox->address = $_POST['name'];
			$inbox->app = db()->table(\AuthAppModel::class)->get('appID', $_POST['app'])->first(true);
			$inbox->psk = str_replace(['/', '+'], '-', base64_encode(random_bytes(25)));
			$inbox->store();
			
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url('app', 'inbox', 'index'));
		} 
		catch (HTTPMethodException$ex) {
			/*Do nothing, the form will be presented here*/
		}
	}
	
	public function detail(\email\InboxModel$inbox) {
		$this->view->set('inbox', $inbox);
		$this->view->set('samples', db()->table('email\incoming')->get('inbox', $inbox)->setOrder('_id', 'DESC')->range(0, 10));
	}
	
	public function update(\email\InboxModel$inbox) {
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not Posted'); }
			
			$inbox->address = $_POST['name'];
			$inbox->app = db()->table(\AuthAppModel::class)->get('appID', $_POST['app'])->first(true);
			$inbox->store();
			
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url());
		} 
		catch (HTTPMethodException$ex) {
			/*Do nothing, the form will be presented here*/
		}
		
		$this->view->set('inbox', $inbox);
	}
	
	public function delete(\email\InboxModel$inbox, $confirm = null) {
		
		$xsrf = new spitfire\io\XSSToken();
		
		if ($confirm && $xsrf->verify($confirm)) {
			$inbox->delete();
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url('app', 'inbox', 'index'));
		}
		
		$this->view->set('token', $xsrf->getValue());
		$this->view->set('inbox', $inbox);
	}
	
}