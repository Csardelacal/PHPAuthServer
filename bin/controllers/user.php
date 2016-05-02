<?php

use spitfire\exceptions\PublicException;
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
			$validatorEmail    = validate()->addRule(new FilterValidationRule(FILTER_VALIDATE_EMAIL, 'Invalid email found'));
			$validatorPassword = validate()->addRule(new MinLengthValidationRule(8, 'Password must have 8 or more characters'));
			
			validate(
					$validatorEmail->setValue(_def($_POST['email'], '')), 
					$validatorUsername->setValue(_def($_POST['username'], '')), 
					$validatorPassword->setValue(_def($_POST['password'], '')));
			
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
			
			$s = new session();
			$s->lock($user->__id);
			
			if (isset($_GET['returnto']) && Strings::startsWith($_GET['returnto'], '/')) 
				{ $this->response->getHeaders()->redirect($_GET['returnto']); }
			
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
				$session = new session();
				$session->lock($user->_id);
			
				if (isset($_GET['returnto']) && Strings::startsWith($_GET['returnto'], '/')) 
					{ return $this->response->getHeaders()->redirect($_GET['returnto']); }
				
				return $this->response->getHeaders()->redirect(new URL());
			} else {
				$this->view->set('message', 'Username or password did not match');
			}
		}
		
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
		
		if ($token && $this->request->isPost()) {
			#Store the new password
			if ($_POST['password'][0] !== $_POST['password'][1]) {
				//TODO: Handle
			}
			
			$token->user->setPassword($_POST['password'])->store();
			return $this->response->getHeaders()->redirect(new URL());
			
		}
		elseif ($token) {
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
	
}
