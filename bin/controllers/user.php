<?php

use client\LocationModel;
use defer\IncinerateSessionTask;
use exceptions\suspension\LoginDisabledException;
use mail\MailUtils;
use mail\spam\domain\implementation\SpamDomainModelReader;
use mail\spam\domain\SpamDomainValidationRule;
use spitfire\core\Environment;
use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
use spitfire\io\curl\URLReflection;
use spitfire\io\session\Session;
use spitfire\storage\database\pagination\Paginator;
use spitfire\validation\rules\FilterValidationRule;
use spitfire\validation\rules\MaxLengthValidationRule;
use spitfire\validation\rules\MinLengthValidationRule;
use spitfire\validation\rules\RegexValidationRule;
use spitfire\validation\ValidationException;

class UserController extends BaseController
{
	
	public function index() {
		$query = db()->table('user')->get('disabled', null, 'IS');
		$paginator = new Paginator($query);
		
		if (isset($_GET['q'])) {
			$query->where('usernames', db()->table('username')->getAll()->where('name', 'LIKE', $_GET['q'] . '%'));
		}
		
		$this->view->set('page.title', 'User list');
		$this->view->set('users', $paginator->records());
		$this->view->set('pagination', $paginator);
		
	}
	
	/**
	 * 
	 * @layout minimal.php
	 * @return type
	 * @throws HTTPMethodException
	 * @throws ValidationException
	 */
	public function register() {
		
		if (isset($_GET['returnto']) && (Strings::startsWith($_GET['returnto'], '/') || filter_var($_GET['input'], FILTER_VALIDATE_EMAIL))) {
			$returnto = $_GET['returnto']; 
		}
		else {
			$returnto = (string)url();
		}
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException(); }
			
			/*
			 * We need to validate the data the user sends. This is a delicate process
			 * and therefore requires quite a lot of attention
			 */
			$validatorUsername = validate()->addRule(new MinLengthValidationRule(4, 'Username must be more than 3 characters'));
			$validatorUsername->addRule(new MaxLengthValidationRule(20, 'Username must be shorter than 20 characters'));
			$validatorUsername->addRule(new RegexValidationRule('/^[a-zA-Z][a-zA-Z0-9\-\_]+$/', 'Username must only contain characters, numbers, underscores and hyphens'));
			$validatorEmail    = validate()->addRule(new FilterValidationRule(FILTER_VALIDATE_EMAIL, 'Invalid email found'));
			$validatorEmail->addRule(new MaxLengthValidationRule(50, 'Email cannot be longer than 50 characters'));
			$validatorEmail->addRule(new SpamDomainValidationRule(new SpamDomainModelReader(db())));
			$validatorPassword = validate()->addRule(new MinLengthValidationRule(8, 'Password must have 8 or more characters'));
			
			validate(
					$validatorEmail->setValue(_def($_POST['email'], '')), 
					$validatorUsername->setValue(_def($_POST['username'], '')), 
					$validatorPassword->setValue(_def($_POST['password'], '')));
			
			$exists = db()->table('username')
				->get('name', $_POST['username'])
				->group()
					->addRestriction('expires', null, 'IS')
					->addRestriction('expires', time(), '>')
				->endGroup()
				->fetch();
			
			if ($exists) {
				throw new ValidationException('Username is taken', 0, Array('Username is taken'));
			}
			
			if (db()->table('user')->get('email', $_POST['email'])->fetch()) {
				throw new ValidationException('Email is taken', 0, Array('Email is already in use'));
			}
			
			/**
			 * Once we validated the data, let's move onto the next step, store the 
			 * data.
			 *
			 * @var $user UserModel
			 */
			$user = db()->table('user')->newRecord();
			$user->email    = $_POST['email'];
			$user->verified = false;
			$user->created  = time();
			$user->setPassword($_POST['password']);
			$user->store();
			
			//TODO: Email needs to be properly stored as a contact
			
			
			$username = db()->table('username')->newRecord();
			$username->user = $user;
			$username->name = $_POST['username'];
			$username->store();
			
			$s = Session::getInstance();
			$s->lock($user->_id);
			
			return $this->response->getHeaders()->redirect($returnto);
		} 
		catch(HTTPMethodException$e) { /*Do nothing, we'll show the form*/}
		catch(ValidationException$e) { $this->view->set('messages', $e->getResult()); }
		
		
		$this->view->set('returnto', $returnto);
	}
	
	/**
	 * 
	 * @layout minimal.php
	 * @throws PublicException
	 */
	public function login() {
		
		if (isset($_GET['returnto']) && Strings::startsWith($_GET['returnto'], '/')) { 
			$returnto = $_GET['returnto']; 
		}
		else {
			$returnto = (string)url();
		}
		
		if ($this->session && $this->level->count() < 1) {
			return $this->response->setBody('Redirect')->getHeaders()->redirect(url('auth', 'threshold', 1, ['returnto' => strval(\spitfire\core\http\URL::current())]));
		}
		
		if ($this->session && $this->level->count() > 0) {
			$this->session->user = $this->session->candidate;
			$this->session->store();
			
			return $this->response->setBody('Redirect')->getHeaders()->redirect($returnto);
		}
					
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			
			$query = db()->table('user')->getAll();
			
			$query->group()
					  ->addRestriction('email', $_POST['username'])
					  ->addRestriction('usernames', db()->table('username')->get('name', $_POST['username'])->addRestriction('expires', NULL, 'IS'))
					->endGroup();
			
			$user = $query->fetch();
			
			#Check whether the user was banned
			$banned     = $user? db()->table('user\suspension')->get('user', $user)->addRestriction('expires', time(), '>')->addRestriction('preventLogin', 1)->fetch() : false;
			if ($banned) { throw new LoginDisabledException($banned); }
			
			
			if ($user && $user->disabled !== null) {
				throw new PublicException('This account has been disabled permanently.', 401);
			}
			elseif ($user) {
				$dbsession = db()->table('session')->newRecord();
				$dbsession->user   = null;
				$dbsession->candidate  = $user;
				$dbsession->device = DeviceModel::makeFromRequest();
				$dbsession->ip = isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR']: $_SERVER['REMOTE_ADDR'];
				
				/*
				 * Retrieve the IP information from the client. This should allow the 
				 * application to provide the user with data where they connected from.
				 */
				$ip = IP::makeLocation();
				if ($ip->country_code) {
					$dbsession->location = LocationModel::getLocation($ip->country_code, substr($ip->city, 0, 50));
				}
				
				$dbsession->store();
				
				$session = Session::getInstance();
				$session->lock($dbsession->_id);
				
				//async()->defer(time() + 86400 * 90, new IncinerateSessionTask($dbsession->_id));
				
				return $this->response->setBody('Redirect')->getHeaders()->redirect(url('auth', 'threshold', 1, ['returnto' => strval(\spitfire\core\http\URL::current())]));
				
			} else {
				$this->view->set('message', 'Username or password did not match');
			}
		}
		
		$this->view->set('returnto', $returnto);
	}
	
	public function logout() {
		$s = Session::getInstance();
		
		$dbsession = db()->table('session')->get('_id', $s->sessionId())->first();
		$token = isset($_GET['token'])? db()->table('access\token')->get('_id', $_GET['token'])->first() : null;
		$rtt = URLReflection::fromURL($_GET['returnto']?? null);
		
		$s->destroy();
		
		if ($dbsession) {
			$dbsession->expires = time();
			$dbsession->store();
			
			/*
			 * When a session is terminated, we clean it up after about twenty minutes,
			 * this gives the system more than enough time to perform some administrative
			 * tasks while maintaining the reference.
			 */
			async()->defer(time() + 1200, new IncinerateSessionTask($dbsession->_id));
			
			/*
			 * It's absolutely imperative for a good user experience that the server
			 * sends a logout command for the session to all clients that depend on
			 * this session. Otherwise they will keep logged into the application and
			 * the sessions will get fractured (some clients maintain an old session).
			 */
			async()->defer(time(), new defer\notify\EndSessionTask($dbsession->_id));
		}
		
		if ($token) {
			$locations = db()->table('client\location')->get('client', $token->client)->all();
			#TODO: This should be extracted to a function with a proper name
			$accept = $locations->filter(function (LocationModel $e) use ($rtt) {
				if ($rtt->getPassword() || $rtt->getUser()) { return false; }
				if ($rtt->getProtocol() !== $e->protocol) { return false; }
				if ($rtt->getServer() !== $e->hostname) { return false; }
				if (!Strings::startsWith($rtt->getPath(), $e->path)) { return false; }
				return true;
			});
			
			if (!$accept) { $rtt = false; }
		}
		else {
			$rtt = false;
		}
		
		$this->response->setBody('Redirect...');
		#TODO: Actually the system should be redirecting to a location that waits for the
		#session to be properly terminated before continuing.
		#At that stage leaking the OIDC session id would be irrelevant, since it's already
		#been terminated and therefore cannot be used for anything but checking whether
		#the response was successful.
		return $this->response->getHeaders()->redirect($rtt? strval($rtt) : url());
	}
	
	public function detail($userid) {
		
		#Check whether the request is from either an admin account or an application
		#All other profiles do have no access to this information
		if (!$this->authapp && !$this->isAdmin) {
			throw new PublicException('You have no privileges to access this data.', 403);
		}
		
		#Get the affected profile
		$profile = db()->table('user')->get('_id', $userid)->fetch()? :
				db()->table('user')->get('usernames', db()->table('username')->get('name', $userid)->
						group()->addRestriction('expires', NULL, 'IS')->addRestriction('expires', time(), '>')->endGroup())->first();
		
		#If there was no profile. Throw an error
		if (!$profile) { throw new PublicException('No user found', 404); }
		
		#Get the list of attributes
		#TODO: Remove, deprecated
		$attributes = db()->table('attribute')->getAll()->all();
		$userAttr   = collect();
		
		foreach ($attributes as $attr) {
			$userAttr[$attr->_id] = db()->table('user\attribute')->get('user', $profile)->where('attr', $attr)->first();
		}
		
		#Get the currently active moderative issue
		#Check if the user has been either banned or suspended
		$suspension = db()->table('user\suspension')->get('user', $profile)->addRestriction('expires', time(), '>')->first();
		
		$this->view->set('user', $profile);
		$this->view->set('profile', $userAttr);
		$this->view->set('attributes', $attributes);
		$this->view->set('suspension', $suspension);
		$this->view->set('email', $this->authapp->email);
	}
	
	
	/**
	 * 
	 * @layout minimal.php
	 * @return type
	 * @throws PublicException
	 */
	public function recover($tokenid = null) {
		
		$token = $tokenid? db()->table('token')->get('token', $tokenid)->fetch() : null;
		$returnto = isset($_GET['returnto'])? $_GET['returnto'] : url();
		
		if ($token && $token->app !== null) {
			throw new PublicException('Token level insufficient', 403);
		}
		
		if ($token && $this->request->isPost() && $_POST['password'][0] === $_POST['password'][1] ) {
			#Store the new password
			$token->user->setPassword($_POST['password'][0])->store();
			return $this->response->getHeaders()->redirect($returnto);
		}
		elseif ($token) { //The user clicked on the recovery email
			#Let the user enter a new password
			$this->view->set('action', 'passwordform');
			$this->view->set('user', $token->user);
		} 
		elseif ($this->request->isPost()) {
			#Tell the user the email was dispatched
			$user = isset($_POST['email'])? db()->table('user')->get('email', $_POST['email'])->fetch() : null;
			
			if ($user) {
				$token = TokenModel::create(null, 1800, false);
				$token->user = $user;
				$token->store();
				$url   = url('user', 'recover', $token->token, ['returnto' => $returnto])->absolute();
				EmailModel::queue($user->email, 'Recover your password', sprintf('Click here to recover your password: <a href="%s">%s</a>', $url, $url));
				
				$this->view->set('success', 'An email with the link to recover your account was sent to you.');
			}
			else {
				$this->view->set('error', 'That email address is not attached to any account');
			}
			
			$this->view->set('action', 'emailform');
			$this->view->set('user', $user);
		} 
		else {
			#Show instructions to recover your password
			$this->view->set('action', 'emailform');
		}
		
		$this->view->set('returnto', $returnto);
	}
	
	public function activate($tokenid = null) {
		$token = $tokenid? db()->table('token')->get('token', $tokenid)->fetch() : null;
		
		#The token should have been created by the Auth Server
		if ($token && $token->app !== null) {
			throw new PublicException('Token level insufficient', 403);
		}
		
		if ($token) {
			$token->user->verified = 1;
			$token->user->store();
		}
		elseif($this->user || $token->user) {
			$token = TokenModel::create(null, 1800, false);
			$token->user = $this->user? : $token->user;
			$token->store();
			$url   = url('user', 'activate', $token->token)->absolute();
			
			/*
			 * If the email the user is using to activate this account is not the 
			 * canonical for their account, we need to make sure that they're not
			 * using a provider thatallows them to bypass verification systems.
			 * 
			 * This is a common behavior for temporary email providers that generate
			 * gmail addresses (they take advantage of the fact that gmail allows 
			 * incoming emails to be routed to the same account by just adding stops,
			 * making email@gmail.com exactly equal to e.mail@gmail.com and ema.il@gmail.com)
			 */
			$canonical = MailUtils::canonicalize($this->user->email, true) !== $this->user->email;
			$delay = $canonical? (Environment::get('phpas.email.delaynoncanonical')?: 45 * 60) : 0;
			
			EmailModel::queue($this->user->email, 'Activate your account', 
					  sprintf('Click here to activate your account: <a href="%s">%s</a>', $url, $url), time() + $delay);
		}
		else {
			throw new PublicException('Not logged in', 403);
		}
		
		#We need to redirect the user back to the home page
		$this->response->setBody('Redirect...')->getHeaders()->redirect(url(Array('message' => 'success')));
	}
	
}
