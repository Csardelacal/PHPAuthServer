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
	
	//TODO: Recheck this whole thing
	public function send($userid = null) {
		if (!$this->user && !$userid) { throw new PublicException('This enpoint requires a token or session'); }
		
		if ($userid && !$this->user) { 
			$app = db()->table('authapp')->get('appID', $_GET['appId'])->addRestriction('appSecret', $_GET['appSecret'])->fetch();
		}
		
		if (!$app && !$this->user) { throw new Exception('Could not relay email to specified user'); }
		
		try {
			#Check if the request is post and subject and body are not empty
			if (!$this->request->isPost()) { throw new spitfire\exceptions\HTTPMethodException(); }
			
			$vsubject = validate()->addRule(new spitfire\validation\EmptyValidationRule('Subject cannot be empty'));
			$vcontent = validate()->addRule(new spitfire\validation\EmptyValidationRule('Message body cannot be empty'));
			validate($vsubject->setValue($_POST['subject']), $vcontent->setValue($_POST['body']));
			
			#Create the message and put it into the message queue
			$user= db()->table('user')->get('_id', _def($_POST['to'], $userid))->fetch()? : $this->user;
			EmailModel::queue($user->email, $vsubject->getValue(), $vcontent->getValue())->store();
			
			#Everything was okay - that's it. The email will be delivered later
		} 
		catch (spitfire\validation\ValidationException$e)  {
			$this->view->set('errors', $e->getResult());
		}
		catch (\spitfire\exceptions\HTTPMethodException$e) {
			//Do nothing, we'll serve it with get
		}
	} 
	
}
