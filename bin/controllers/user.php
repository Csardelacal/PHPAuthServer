<?php

use spitfire\exceptions\PublicException;
use spitfire\io\session\Session;
use spitfire\validation\FilterValidationRule;
use spitfire\validation\MinLengthValidationRule;

class UserController extends BaseController
{
	
	public function index() {
		$query = db()->table('user')->get('disabled', null, 'IS');
		$paginator = new Pagination($query);
		
		$this->view->set('page.title', 'User list');
		$this->view->set('users', $query->fetchAll());
		$this->view->set('pagination', $paginator);
		
	}
	
	public function register() {
		
		$query = db()->table('attribute')->get('writable', Array('public', 'groups', 'related', 'me'));
		$query->addRestriction('required', true);
		$attributes = $query->fetchAll();
		
		
		if ($this->request->isPost()) {
			
			/*
			 * We need to validate the data the user sends. This is a delicate process
			 * and therefore requires quite a lot of attention
			 */
			$validatorUsername = validate()->addRule(new MinLengthValidationRule(4, 'Username must be more than 3 characters'));
			$validatorUsername->addRule(new \spitfire\validation\RegexValidationRule('^[a-zA-z][a-zA-z0-9\-\_]+$', 'Username must only contain characters, numbers, underscores and hyphens'));
			$validatorEmail    = validate()->addRule(new FilterValidationRule(FILTER_VALIDATE_EMAIL, 'Invalid email found'));
			$validatorPassword = validate()->addRule(new MinLengthValidationRule(8, 'Password must have 8 or more characters'));
			
			validate(
					$validatorEmail->setValue(_def($_POST['email'], '')), 
					$validatorUsername->setValue(_def($_POST['username'], '')), 
					$validatorPassword->setValue(_def($_POST['password'], '')));
			
			if (db()->table('username')->get('name', $_POST['username'])->fetch()) {
				throw new \spitfire\validation\ValidationException('Username is taken', 0, Array('Username is taken'));
			}
			
			/**
			 * Once we validated the data, let's move onto the next step, store the 
			 * data.
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
			
			if (isset($_GET['returnto'])) 
				{ return $this->response->getHeaders()->redirect($_GET['returnto']); }
			
			return $this->response->getHeaders()->redirect((string)new URL('user', 'dashboard'));
		}
		
		
		$this->view->set('attributes', $attributes);
	}
	
	public function login() {
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			
			$query = db()->table('user')->getAll();
			
			$query->group()
					  ->addRestriction('email', $_POST['username'])
					  ->addRestriction('usernames', db()->table('username')->get('name', $_POST['username'])->addRestriction('expires', NULL, 'IS'))
					->endGroup();
			
			$user = $query->fetch();
			
			if ($user && $user->checkPassword($_POST['password'])) {
				$session = Session::getInstance();
				$session->lock($user->_id);
			
				if (isset($_GET['returnto']) && Strings::startsWith($_GET['returnto'], '/')) 
					{ return $this->response->getHeaders()->redirect($_GET['returnto']); }
				
				return $this->response->getHeaders()->redirect(new URL());
			} else {
				$this->view->set('message', 'Username or password did not match');
			}
		}
		
	}
	
	public function logout() {
		$s = Session::getInstance();
		$s->destroy();
		
		return $this->response->getHeaders()->redirect(new URL());
	}
	
	public function detail($userid) {
		
		if ($_GET['token']) { $token = db()->table('token')->get('token', $_GET['token'])->fetch(); }
		else                { $token = null; }
		
		if ($token !== null && $token->expires !== null && $token->expires !== '' && $token->expires < time()) 
			{ throw new PublicException('Your token is expired', 401); }
		
		#Get the affected profile
		$profile = db()->table('user')->get('_id', $userid)->fetch()? :
				db()->table('user')->get('usernames', db()->table('username')->get('name', $userid)->
						group()->addRestriction('expires', NULL, 'IS')->addRestriction('expires', time(), '>')->endGroup())->fetch();
		
		#If there was no profile. Throw an error
		if (!$profile) { throw new PublicException('No user found', 404); }
		
		#Set the base permissions
		$permissions = Array('public');
		
		#Check if the two users are in the same group
		if ($token !== null && $token->user !== null) {
			$groupquery = db()->table('group')->getAll();
			$groupquery->addRestriction('members', db()->table('user\group')->get('user', $profile->getQuery()));
			$groupquery->addRestriction('members', db()->table('user\group')->get('user', $token->user));
			
			$groups = $groupquery->fetchAll();
			if (isset($groups[0])) { $permissions[] = 'groups'; }
		}
		
		#Check if the user is himself
		if ($token && $token->user && $profile->_id === $token->user->_id) { $permissions = array_merge($permissions, Array('me', 'groups', 'related')); }
		
		#Check if the user is an administrator
		if ($this->isAdmin) { $permissions = array_merge($permissions, Array('me', 'groups', 'related', 'nem')); }
		
		#If permissions aren't empty, let the system filter those
		if (!empty($permissions)) { $permissions = array_unique($permissions); }
		
		#Get the public attributes
		$attributes = db()->table('attribute')->get('readable', $permissions)->fetchAll();
		
		$this->view->set('profile', $profile);
		$this->view->set('permissions', $permissions);
		$this->view->set('attributes', $attributes);
	}
	
	public function recover($tokenid = null) {
		
		$token = $tokenid? db()->table('token')->get('token', $tokenid)->fetch() : null;
		
		if ($token && $token->app !== null) {
			throw new PublicException('Token level insufficient', 403);
		}
		
		if ($token && $this->request->isPost() && $_POST['password'][0] === $_POST['password'][1] ) {
			#Store the new password
			$token->user->setPassword($_POST['password'])->store();
			return $this->response->getHeaders()->redirect(new URL());
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
				$url   = new absoluteURL('user', 'recover', $token->token);
				EmailModel::queue($user->email, 'Recover your password', sprintf('Click here to recover your password: <a href="%s">%s</a>', $url, $url));
			}
			
			$this->view->set('action', 'emailform');
			$this->view->set('user', $user);
		} 
		else {
			#Show instructions to recover your password
			$this->view->set('action', 'emailform');
		}
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
		else {
			$token = TokenModel::create(null, 1800, false);
			$token->user = $this->user;
			$token->store();
			$url   = new absoluteURL('user', 'activate', $token->token);
			EmailModel::queue($this->user->email, 'Activate your account', 
					  sprintf('Click here to activate your account: <a href="%s">%s</a>', $url, $url));
		}
		
		#We need to redirect the user back to the home page
		$this->response->getHeaders()->redirect(new URL(Array('message' => 'success')));
	}
	
}
