<?php

use spitfire\exceptions\PublicException;
use spitfire\validation\FilterValidationRule;
use spitfire\validation\MinLengthValidationRule;

class UserController extends BaseController
{
	
	
	
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
				db()->table('user')->get('usernames', db()->table('username')->get('name', $userid)->addRestriction('expires', NULL, 'IS'))->fetch();
		
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
	
}
