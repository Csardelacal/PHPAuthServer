<?php

use app\AttributeLock;
use mail\spam\domain\implementation\SpamDomainModelReader;
use mail\spam\domain\SpamDomainValidationRule;
use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
use spitfire\io\session\Session;
use spitfire\storage\database\pagination\Paginator;
use spitfire\validation\FilterValidationRule;
use spitfire\validation\MaxLengthValidationRule;
use spitfire\validation\MinLengthValidationRule;
use spitfire\validation\RegexValidationRule;
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
		
		$query = db()->table('attribute')->get('writable', Array('public', 'groups', 'related', 'me'));
		$query->addRestriction('required', true);
		$attributes = $query->fetchAll();
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException(); }
			
			/*
			 * We need to validate the data the user sends. This is a delicate process
			 * and therefore requires quite a lot of attention
			 */
			$validatorUsername = validate()->addRule(new MinLengthValidationRule(4, 'Username must be more than 3 characters'));
			$validatorUsername->addRule(new MaxLengthValidationRule(20, 'Username must be shorter than 20 characters'));
			$validatorUsername->addRule(new RegexValidationRule('/^[a-zA-z][a-zA-z0-9\-\_]+$/', 'Username must only contain characters, numbers, underscores and hyphens'));
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
			$user->password = $_POST['password'];
			$user->verified = false;
			$user->created  = time();
			$user->store();
			
			$username = db()->table('username')->newRecord();
			$username->user = $user;
			$username->name = $_POST['username'];
			$username->store();
			
			foreach($attributes as $attribute) {
				$userattribute = db()->table('user\attribute')->newRecord();
				$userattribute->user = $user;
				$userattribute->attr = $attribute;
				$userattribute->value = $_POST[$attribute->_id];
				$userattribute->store();
			}
			
			$s = Session::getInstance();
			$s->lock($user->_id);
			
			return $this->response->getHeaders()->redirect($returnto);
		} 
		catch(HTTPMethodException$e) { /*Do nothing, we'll show the form*/}
		catch(ValidationException$e) { $this->view->set('messages', $e->getResult()); }
		
		
		$this->view->set('attributes', $attributes);
		$this->view->set('returnto', $returnto);
	}
	
	/**
	 * 
	 * @layout minimal.php
	 * @return type
	 * @throws PublicException
	 */
	public function login() {
		
		if (isset($_GET['returnto']) && Strings::startsWith($_GET['returnto'], '/')) { 
			$returnto = $_GET['returnto']; 
		}
		else {
			$returnto = (string)url();
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
			if ($banned) { throw new PublicException('Your account was banned, login was disabled.' . $banned->reason, 401); }
			
			
			if ($user && $user->disabled !== null) {
				throw new PublicException('This account has been disabled permanently.', 401);
			}
			elseif ($user && $user->checkPassword($_POST['password'])) {
				$session = Session::getInstance();
				$session->lock($user->_id);
				
				return $this->response->getHeaders()->redirect($returnto);
			} else {
				$this->view->set('message', 'Username or password did not match');
			}
		}
		
		$this->view->set('returnto', $returnto);
	}
	
	public function logout() {
		$s = Session::getInstance();
		$s->destroy();
		
		return $this->response->getHeaders()->redirect(new URL());
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
		$attributes = db()->table('attribute')->getAll()->all();
		$userAttr   = collect();
		
		foreach ($attributes as $attr) {
			$lock = new AttributeLock($attr, $profile);
			
			/*
			 * Depending on whether the user is an administrator or an app that can
			 * unlock the attribute, we add the data to the list.
			 */
			if ($this->isAdmin || $lock->unlock($this->authapp)) {
				$userAttr[$attr->_id] = db()->table('user\attribute')->get('user', $profile)->where('attr', $attr)->first();
			}
		}
		
		#Get the currently active moderative issue
		#Check if the user has been either banned or suspended
		$suspension = db()->table('user\suspension')->get('user', $profile)->addRestriction('expires', time(), '>')->fetch();
		
		$this->view->set('user', $profile);
		$this->view->set('profile', $userAttr);
		$this->view->set('attributes', $attributes);
		$this->view->set('suspension', $suspension);
	}
	
	
	/**
	 * 
	 * @layout minimal.php
	 * @return type
	 * @throws PublicException
	 */
	public function recover(TokenModel$token = null) {
		
		if (isset($_GET['returnto']) && Strings::startsWith($_GET['returnto'], '/')) { 
			$returnto = $_GET['returnto']; 
		}
		else {
			$returnto = (string)url();
		}
		
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
			EmailModel::queue($this->user->email, 'Activate your account', 
					  sprintf('Click here to activate your account: <a href="%s">%s</a>', $url, $url));
		}
		else {
			throw new PublicException('Not logged in', 403);
		}
		
		#We need to redirect the user back to the home page
		$this->response->getHeaders()->redirect(url(Array('message' => 'success')));
	}
	
}
