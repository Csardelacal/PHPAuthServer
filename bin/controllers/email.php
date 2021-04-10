<?php

use spitfire\core\http\URL;
use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;

/**
 * The email controller is one of the weirder controllers in PHPAuthServer. This
 * is due to the fact that the system is not intended to deliver email and it only
 * provides this service to ensure that it completely obfuscates the user's email
 * from the apps which use it to log in and authenticate users.
 */
class EmailController extends BaseController
{
	
	public function _onload() {
		parent::_onload();
		
		if ($this->context->action == 'send') {
			//This needs to be here until the send method is gone.
		}
		
		/*
		 * If the user is not logged in, we will ask them to do so. Otherwise this
		 * section is useless to them.
		 */
		elseif (!$this->user) {
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url('user', 'login', ['returnto' => (string) URL::current()]));
			return;
		}
	}
	
	/**
	 * Allows the user to see which email addresses are connected to their account.
	 */
	public function index() {
		$emails = db()->table('passport')->get('user', $this->user)->where('type', 'email')->all();
		$this->view->set('emails', $emails);
	}
	
	/**
	 * 
	 * @validate >> POST#email(string required)
	 * @throws HTTPMethodException
	 */
	public function create() {
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not Posted'); }
			if (!$this->validation->isEmpty()) { throw new ValidationException('Validation failed', 2005121355, $this->validation->toArray()); }
			
			/*
			 * For the sake of validation, we canonicalize email addresses before searching
			 * for duplicates. This prevents users from abusing the system by adding
			 * namespace email addresses to the system.
			 * 
			 * Since it's common practice to ignore the fragment fo an email address
			 * (the part after a plus sign) and the stops inside it, we will do so
			 * too. This may lead to the system rejecting email addresses that are
			 * similar but different to an email server, but they will then either
			 * be extremely generic or attempts to imporsonate someone regardless.
			 */
			$canonical = \mail\MailUtils::canonicalize($_POST['email'], true);
			
			if (db()->table('passport')->get('canonical', $canonical)->first()) { 
				throw new ValidationException('Validation failed', 2005121355, ['Email is already registered']); 
			}
			
			$passport = db()->table('passport')->newRecord();
			$passport->user = $this->user;
			$passport->type = 'email';
			$passport->content = $_POST['email'];
			$passport->canonical = $canonical;
			$passport->login = true;
			
			/*
			 * If the user does not verify the email within 30 days, we will remove 
			 * the record. This prevents users from locking other people out of 
			 * registration by registering an account with an email they do not 
			 * own and just abandoning the account.
			 */
			$passport->expires = time() + 86400 * 30;
			$passport->store();
			
			$auth = db()->table('authentication\provider')->newRecord();
			$auth->user = $this->user;
			$auth->type = \authentication\ProviderModel::TYPE_EMAIL;
			$auth->passport = $passport;
			$auth->content = $_POST['email'];
			$auth->preferred = false;
			$auth->expires = time() + 86400 * 30;
			$auth->store();
			
			//TODO: Verification procedures need to be initiated
		} 
		catch (HTTPMethodException $ex) {
			# Do nothing, just show the form to the user
		}
	}
	
	public function remove(PassportModel$email) {
		//TODO: Check for strong authentication
		$auth    = db()->table('authentication\provider')->get('passport', $email)->first();
		
		$auth->expires    = time() + 90 * 86400;
		$email->expires   = time() + 90 * 86400;
		
		$auth->store();
		$email->store();
	}
	
	public function twofactor(\authentication\ProviderModel$email) {
		
		if ($email->type != \authentication\ProviderModel::TYPE_EMAIL) {
			throw new PublicException('Invalid provider', 400);
		}
		
		$self = db()->table('authapp')->get('_id', SysSettingModel::getValue('app.self'))->first();
		$relay = db()->table('authapp')->get('_id', SysSettingModel::getValue('app.email'))->first();
		$url  = rtrim($relay->url, '\/');
		
		$twofactor = \authentication\ChallengeModel::make($email);
		$to = ':' . $email->user->_id;
		
		/**
		 * @todo This code needs to be refactored to conform with proper standards. Right now it would not
		 * work appropriately.
		 */
		$request = request($url . '/message/create.json');
		$request->get('signature', (string)$this->signature->make($self->appID, $self->appSecret, $relay->appID));
		$request->post('to', $to);
		$request->post('subject', 'Your two factor authentication code');
		$request->post('html', 'Your two factor authentication code is ' . $twofactor->secret . ' or <a href="' . url('twofactor', 'check', $email->_id, $twofactor->secret, ['returnto' => $_GET['returnto']?? '/'])->absolute() . '">click here</a>');

		$response = $request->send()->expect(200)->json();
		$mid = $response->payload->id;

		$request3 = request($url . '/email/create/' . $mid .'.json');
		$request3->get('signature', (string)$this->signature->make($self->appID, $self->appSecret, $relay->appID));
		$request3->post('meta', time());

		$response3 = $request3->send()->expect(200)->json();
		$id = $response3->id;

		$request2 = request($url . '/email/send/' . $id . '.json');
		$request2->get('signature', $this->signature->make($self->appID, $self->appSecret, $relay->appID));
		
		$request2->send();
		
		$this->response->setBody('Redirect...')->getHeaders()->redirect(url('twofactor', 'check', $email->_id, ['returnto' => $_GET['returnto']?? '/']));
		
	}

	public function verify(PassportModel$email, $token = null) {
		
	}
	
	/**
	 * Redirects the request to relay. This allows old applications to send messages
	 * the way they were used to, but will eventually be deprecated
	 * 
	 * @deprecated since version 2020-05-12
	 * @param int $userid Deprecated, do not use
	 * @throws PublicException
	 * @throws Exception
	 * @throws HTTPMethodException
	 */
	public function send($userid = null) {
		$self  = db()->table('authapp')->get('_id', SysSettingModel::getValue('app.self'))->first();
		$email = db()->table('authapp')->get('_id', SysSettingModel::getValue('app.email'))->first();
		
		$url = rtrim($email->url, '\/');
		
		
		/*
		 * Get the application authorizing the email. Although we do not log this 
		 * right now, it's gonna be invaluable to help determining whether an app
		 * was compromised and is sending garbage.
		 * 
		 * This is concerning, the application should not be using the token, since
		 * that is known information. I'm locking down the ability to send like this.
		 */
		$app = db()->table('authapp')->get('appID', $_GET['appId'])->addRestriction('appSecret', $_GET['appSecret'])->fetch(true);
		
		if (!$email->url) {
			throw new PublicException('Relay has no URL', 202006051537);
		}
		
		if (filter_var($_POST['to']?? $userid, FILTER_VALIDATE_EMAIL)) {
			$to = $_POST['to']?? $userid;
		}
		else {
			$to = ':' . ($_POST['to']?? $userid);
		}
		
		$request = request($url . '/message/create.json');
		$request->get('signature', (string)$this->signature->make($app->appID, $app->appSecret, $email->appID));
		$request->post('to', $to);
		$request->post('subject', $_POST['subject']);
		$request->post('html', $_POST['body']);
		
		$response = $request->send()->expect(200)->json();
		$mid = $response->payload->id;
		
		$request3 = request($url . '/outbox/send/' . $mid .'.json');
		$request3->get('signature', (string)$this->signature->make($app->appID, $app->appSecret, $email->appID));
		$request3->post('meta', time());
		
		$response3 = $request3->send()->expect(200)->json();
	} 
	
}
