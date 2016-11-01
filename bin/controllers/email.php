<?php

use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
use spitfire\validation\EmptyValidationRule;
use spitfire\validation\ValidationException;

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
	
	/**
	 * 
	 * GET Parameters:
	 * - appId     - Id of the app trying to relay the message
	 * - appSecret - App Secret to authenticate the App
	 * - userId    - Either a valid email or a user id
	 * 
	 * @todo  Introduce email permissions for certain applications
	 * @param int $userid Deprecated, do not use
	 * @throws PublicException
	 * @throws Exception
	 * @throws HTTPMethodException
	 */
	public function send($userid = null) {
		
		//TODO: Add search by username
		
		try {
			#Check if the request is post and subject and body are not empty
			if (!$this->request->isPost()) { throw new HTTPMethodException(); }
			
			/*
			 * Retrieve the email / userId from the request. This should either be posted
			 * or getted. 
			 */
			$userid = isset($_GET['to'])? $_GET['to'] : _def($_POST['to'], $userid);

			/*
			 * We check whether we received any data at all via POST for the recipient.
			 * We can obviously not relay any email to any user if we don't know where
			 * to send it to.
			 */
			if (!$userid) { 
				throw new PublicException('This enpoint requires a recipient'); 
			}

			/*
			 * Get the application authorizing the email. Although we do not log this 
			 * right now, it's gonna be invaluable to help determining whether an app
			 * was compromised and is sending garbage.
			 */
			if (!$this->token) { 
				$app = db()->table('authapp')->get('appID', $_GET['appId'])->addRestriction('appSecret', $_GET['appSecret'])->fetch();
			}
			else {
				$app = $this->token->app;
			}

			if (!$app) { 
				throw new Exception('Could not authenticate the application trying to send the email'); 
			}

			/*
			 * Determine what kind of id you were sent to determine where to send the 
			 * email to.
			 */
			if (filter_var($userid, FILTER_VALIDATE_EMAIL)) {
				$email = $userid;
			}
			elseif(is_numeric($userid)) {
				$user  = db()->table('user')->get('_id', _def($_POST['to'], $userid))->fetch();
				$email = $user->email;
			}
			
			$vsubject = validate()->addRule(new EmptyValidationRule('Subject cannot be empty'));
			$vcontent = validate()->addRule(new EmptyValidationRule('Message body cannot be empty'));
			validate($vsubject->setValue($_POST['subject']), $vcontent->setValue($_POST['body']));
			
			#Create the message and put it into the message queue
			EmailModel::queue($email, $vsubject->getValue(), $vcontent->getValue())->store();
			
			#Everything was okay - that's it. The email will be delivered later
		} 
		catch (ValidationException$e)  {
			$this->view->set('errors', $e->getResult());
		}
		catch (HTTPMethodException$e) {
			//Do nothing, we'll serve it with get
		}
	} 
	
}
