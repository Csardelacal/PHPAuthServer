<?php

use defer\tasks\EndSessionTask;
use defer\tasks\IncinerateSessionTask;
use mail\spam\domain\implementation\SpamDomainModelReader;
use mail\spam\domain\SpamDomainValidationRule;
use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
use spitfire\io\session\Session;
use spitfire\storage\database\pagination\Paginator;
use spitfire\validation\rules\FilterValidationRule;
use spitfire\validation\rules\MaxLengthValidationRule;
use spitfire\validation\rules\MinLengthValidationRule;
use spitfire\validation\rules\RegexValidationRule;
use spitfire\validation\ValidationError;
use spitfire\validation\ValidationException;

class UserController extends BaseController
{
	
	public function index()
	{
		if (!$this->authapp && !$this->isAdmin) {
			throw new PublicException('You have no privileges to access this data.', 403);
		}
		
		$query = db()->table('user')->get('disabled', null, 'IS');
		$paginator = new Paginator($query);
		
		if (isset($_GET['q']) && !empty($_GET['q'])) {
			$query->group()
				->where('usernames', db()->table('username')->getAll()
					->where('name', 'LIKE', '%' . implode('%', str_split($_GET['q'])) . '%'))
				->where('_id', $_GET['q']);
		}
		
		$this->view->set('page.title', 'User list');
		$this->view->set('users', $paginator->records());
		$this->view->set('pagination', $paginator);
	}
	
	/**
	 *
	 * @layout minimal.php
	 * @return void
	 * @throws HTTPMethodException
	 * @throws ValidationException
	 */
	public function register()
	{
		
		if (isset($_GET['returnto']) && Strings::startsWith($_GET['returnto'], '/')) {
			$returnto = $_GET['returnto'];
		}
		else {
			$returnto = (string)url();
		}
		
		$query = db()->table('attribute')->get('writable', array('public', 'groups', 'related', 'me'));
		$query->addRestriction('required', true);
		$attributes = $query->fetchAll();
		
		try {
			if (!$this->request->isPost()) {
				throw new HTTPMethodException();
			}
			
			/*
			 * We need to validate the data the user sends. This is a delicate process
			 * and therefore requires quite a lot of attention
			 */
			$validatorUsername = validate()->addRule(new MinLengthValidationRule(
				4,
				'Username must be more than 3 characters'
			));
			
			$validatorUsername->addRule(new MaxLengthValidationRule(20, 'Username must be shorter than 20 characters'));
			$validatorUsername->addRule(new RegexValidationRule(
				'/^[a-zA-Z][a-zA-Z0-9\-\_]+$/',
				'Username must only contain characters, numbers, underscores and hyphens'
			));
			
			$validatorEmail = validate()->addRule(new FilterValidationRule(
				FILTER_VALIDATE_EMAIL,
				'Invalid email found'
			));
			$validatorEmail->addRule(new MaxLengthValidationRule(50, 'Email cannot be longer than 50 characters'));
			$validatorEmail->addRule(new SpamDomainValidationRule(new SpamDomainModelReader(db())));
			$validatorPassword = validate()->addRule(new MinLengthValidationRule(
				8,
				'Password must have 8 or more characters'
			));
			
			validate(
				$validatorEmail->setValue(_def($_POST['email'], '')),
				$validatorUsername->setValue(_def($_POST['username'], '')),
				$validatorPassword->setValue(_def($_POST['password'], ''))
			);
			
			$exists = db()->table('username')
				->get('name', $_POST['username'])
				->group()
					->addRestriction('expires', null, 'IS')
					->addRestriction('expires', time(), '>')
				->endGroup()
				->fetch();
			
			if ($exists) {
				throw new ValidationException('Username is taken', 0, array(new ValidationError('Username is taken')));
			}
			
			if (stripos($_POST['email'], 'gmail.com') !== false) {
				list($euser, $edomain) = explode('@', $_POST['email'], 2);
				$_POST['email'] = sprintf('%s@gmail.com', str_replace('.', '', $euser));
			}
			
			if (stripos($_POST['email'], '+') !== false) {
				throw new PublicException('Email containing a plus has been temporarily banned', 400);
			}
			
			
			if (db()->table('user')->get('email', $_POST['email'])->fetch()) {
				throw new ValidationException(
					'Email is taken',
					0,
					[new ValidationError('Email is already in use')]
				);
			}
			
			/**
			 * Once we validated the data, let's move onto the next step, store the
			 * data.
			 *
			 * @var UserModel
			 */
			$user = db()->table('user')->newRecord();
			$user->email    = $_POST['email'];
			$user->verified = false;
			$user->created  = time();
			$user->setPassword($_POST['password']);
			$user->store();
			
			
			$username = db()->table('username')->newRecord();
			$username->user = $user;
			$username->name = $_POST['username'];
			$username->store();
			
			foreach ($attributes as $attribute) {
				$userattribute = db()->table(user\AttributeModel::class)->newRecord();
				$userattribute->user = $user;
				$userattribute->attr = $attribute;
				$userattribute->value = $_POST[$attribute->_id];
				$userattribute->store();
			}
			
			$s = Session::getInstance();
			$s->lock($user->_id);
			
			$this->response->getHeaders()->redirect($returnto);
			return;
		}
		catch (HTTPMethodException$e) {
/*Do nothing, we'll show the form*/
		}
		catch (ValidationException$e) {
			$this->view->set('messages', $e->getResult());
		}
		
		
		$this->view->set('attributes', $attributes);
		$this->view->set('returnto', $returnto);
	}
	
	/**
	 *
	 * @layout minimal.php
	 * @return void
	 * @throws PublicException
	 */
	public function login()
	{
		$session = Session::getInstance();
		
		if ($session->get('IP') && $session->get('IP') !== $_SERVER['REMOTE_ADDR']) {
			$session->destroy();
			$this->response->setBody('Redirecting')->getHeaders()->redirect(url('user', 'login'));
			return;
		}
		
		if (!$session->get('IP')) {
			#Lock the session to the current IP
			$session->set('IP', $_SERVER['REMOTE_ADDR']);
			$session->set('locked', time());
		}
		
		if (isset($_GET['returnto']) && Strings::startsWith($_GET['returnto'], '/')) {
			$returnto = $_GET['returnto'];
		}
		else {
			$returnto = (string)url();
		}
		
		$token = new \spitfire\io\XSSToken();
		$this->view->set('xsrf', $token);
		
		try {
			$token->verify($_POST['_xsrf_']);
			$verified = true;
		}
		catch (PublicException $e) {
			$verified = false;
		}
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && $verified) {
			
			/**
			 * @todo This mechanism is extraordinarily inefficient, since it will require
			 * the application to loop over each username it matches and query for the user
			 * to extract their id. This is a flaw in Spitfire and planned to be resolved in
			 * SF2020 but it's currently doing a lot of extra work.
			 */
			$username_ids = db()->table('username')
				->get('name', trim($_POST['username']))
				->where('expires', null)
				->all()
				->each(fn($u) => $u->user->_id);
			
			$query = db()->table('user')->getAll();
			$query->group()
					  ->where('email', trim($_POST['username']))
					  ->where('_id', $username_ids->toArray()?: null)
					->endGroup();
			
			/**
			 * @var UserModel|null
			 */
			$user = $query->first();
			
			#Check whether the user was banned
			if ($user && $user->isSuspended()) {
				$banned = $user->isSuspended();
				$ex = new LoginException('Your account was suspended, login is disabled.', 401);
				$ex->setUserID($user->_id);
				$ex->setReason($banned->reason);
				$ex->setExpiry($banned->expires);
				throw $ex;
			}
			
			
			if ($user && $user->disabled !== null) {
				$ex = new LoginException('This account has been disabled permanently.', 401);
				$ex->setUserID($user->_id);
				throw $ex;
			}
			elseif ($user && $user->checkPassword($_POST['password'])) {
				
				/**
				 * Create a fresh session whenever a user logs in. Make sure that the session
				 * does not exist on the database yet.
				 */
				$this->session = null;
				
				do {
					session_regenerate_id(true);
					$found = db()->table('session')->get('_id', SessionModel::TOKEN_PREFIX . session_id())->first();
				} while ($found);
				
				/**
				 *
				 * @todo The session mechanism is getting rather big and would benefit from
				 * being extracted from here.
				 */
				$session = Session::getInstance();
				$session->lock($user->_id);
				
				$this->session = db()->table('session')->newRecord();
				$this->session->_id = SessionModel::TOKEN_PREFIX . session_id();
				
				/**
				 * The IP address. In the event of it being forwarded by one (or multiple) proxies,
				 * the IP will be in the X_FORWARDED_FOR header. While the REMOTE_ADDR becomes absolutely
				 * useless.
				 */
				$rawip  = explode(',', $_SERVER["HTTP_X_FORWARDED_FOR"]?? $_SERVER["REMOTE_ADDR"]);
				$ipaddr = trim(array_shift($rawip));
				
				$this->session->ip      = bin2hex(inet_pton($ipaddr));
				$this->session->expires = time() + 365 * 86400;
				$this->session->userTime = $_POST['time'];
				$this->session->locale = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"]?? '', 0, 50);
				
				/*
				* Retrieve the IP information from the client. This should allow the
				* application to provide the user with data where they connected from.
				*
				* @todo While Cloudflare is very convenient. It's definitely not a generic
				* protocol and produces vendor lock-in. This should be replaced with an
				* interface that allows using a different vendor for location detection.
				*/
				$this->session->country = $_SERVER["HTTP_CF_IPCOUNTRY"];
				$this->session->city    = substr($_SERVER["HTTP_CF_IPCITY"], 0, 20);
				$this->session->user = $user;
				$this->session->store();
				
				$this->response->getHeaders()->redirect($returnto);
				return;
			} else {
				$this->view->set('message', 'Username or password did not match');
			}
		}
		
		$this->view->set('returnto', $returnto);
		$this->view->set('xsrf', $token);
	}
	
	public function logout()
	{
		$s = Session::getInstance();
		$s->destroy();
		
		if (isset($_GET['returnto']) && Strings::startsWith($_GET['returnto'], '/')) {
			$returnto = $_GET['returnto'];
		}
		else {
			$returnto = (string)url();
		}
		
		if ($this->session !== null) {
			$this->session->expires = time();
			$this->session->store();
			
			/**
			 * Mark the session to be ended and incinerated
			 */
			$this->defer->defer(1, EndSessionTask::class, $this->session->_id);
			$this->defer->defer(3600, IncinerateSessionTask::class, $this->session->_id);
		}
		
		$this->response->setBody('Redirect...');
		$this->response->getHeaders()->redirect($returnto);
		return;
	}
	
	public function detail($userid)
	{
		
		#Check whether the request is from either an admin account or an application
		#All other profiles do have no access to this information
		if (!$this->authapp && !$this->isAdmin) {
			throw new PublicException('You have no privileges to access this data.', 403);
		}
		
		#Get the affected profile
		$profile = db()->table('user')->get('_id', $userid)->fetch()? :
		db()->table('user')
			->get(
				'usernames',
				db()->table('username')->get('name', $userid)
					->group()->where('expires', null)->where('expires', '>', time())->endGroup()
			)
			->first();
		
		#If there was no profile. Throw an error
		if (!$profile) {
			throw new PublicException('No user found', 404);
		}
		
		#Get the list of attributes
		$attributes = db()->table('attribute')->getAll()->all();
		$userAttr   = collect();
		
		foreach ($attributes as $attr) {
			
			/*
			 * Depending on whether the user is an administrator or an app that can
			 * unlock the attribute, we add the data to the list.
			 */
			#if ($this->isAdmin || $lock->unlock($this->authapp)) {
				$userAttr[$attr->_id] = db()->table(user\AttributeModel::class)
					->get('user', $profile)
					->where('attr', $attr)
					->first();
			#}
		}
		
		#Get the currently active moderative issue
		#Check if the user has been either banned or suspended
		$suspension = db()->table(\user\SuspensionModel::class)
			->get('user', $profile)
			->where('expires', '>', time())
			->first();
		
		$this->view->set('user', $profile);
		$this->view->set('profile', $userAttr);
		$this->view->set('attributes', $attributes);
		$this->view->set('suspension', $suspension);
	}
	
	
	/**
	 *
	 * @layout minimal.php
	 * @return void
	 * @throws PublicException
	 */
	public function recover($tokenid = null)
	{
		
		$token = $tokenid? db()->table('token')->get('token', $tokenid)->fetch() : null;
		$returnto = isset($_GET['returnto'])? $_GET['returnto'] : url();
		
		if ($token && $token->app !== null) {
			throw new PublicException('Token level insufficient', 403);
		}
		
		if ($token && $this->request->isPost() && $_POST['password'][0] === $_POST['password'][1]) {
			#Store the new password
			$token->user->setPassword($_POST['password'][0])->store();
			$this->response->getHeaders()->redirect($returnto);
			return;
		}
		elseif ($token) { //The user clicked on the recovery email
			#Let the user enter a new password
			$this->view->set('action', 'passwordform');
			$this->view->set('user', $token->user);
		}
		elseif ($this->request->isPost()) {
			#Tell the user the email was dispatched
			$user = isset($_POST['email'])? db()->table('user')->get('email', $_POST['email'])->fetch() : null;
			
			//If there was no user to be found, try again with the changes we made to search for spam prevention
			if (!$user) {
				if (stripos($_POST['email'], 'gmail.com') !== false) {
					list($euser, $edomain) = explode('@', $_POST['email'], 2);
					$_POST['email'] = sprintf('%s@gmail.com', str_replace('.', '', $euser));
				}
				
				$user = isset($_POST['email'])? db()->table('user')->get('email', $_POST['email'])->fetch() : null;
			}
			
			if ($user) {
				$token = TokenModel::create(null, 1800, false);
				$token->user = $user;
				$token->store();
				$url   = url('user', 'recover', $token->token, ['returnto' => $returnto])->absolute();
				EmailModel::queue(
					$user->email,
					'Recover your password',
					sprintf('Click here to recover your password: <a href="%s">%s</a>', $url, $url)
				);
				
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
	
	public function activate($tokenid = null)
	{
		$token = $tokenid? db()->table('token')->get('token', $tokenid)->fetch() : null;
		
		#The token should have been created by the Auth Server
		if ($token && $token->app !== null) {
			throw new PublicException('Token level insufficient', 403);
		}
		
		if ($token) {
			$token->user->verified = 1;
			$token->user->store();
		}
		elseif ($this->user) {
			$token = TokenModel::create(null, 1800, false);
			$token->user = $this->user;
			$token->store();
			$url   = url('user', 'activate', $token->token)->absolute();
			EmailModel::queue(
				$this->user->email,
				'Activate your account',
				sprintf('Click here to activate your account: <a href="%s">%s</a>', $url, $url),
				preg_match('/.*[\.\+].*\@.*/', $this->user->email)? time() + 1200 : time() + 300
			);
		}
		else {
			$this->response->setBody('Redirect...')
				->getHeaders()->redirect(url('user', 'login', ['returnto' => strval(url('user', 'activate'))]));
			return;
		}
		
		#We need to redirect the user back to the home page
		$this->response->setBody('Redirect...')->getHeaders()->redirect(url(array('message' => 'success')));
	}
}
