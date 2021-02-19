<?php

use mail\spam\domain\implementation\SpamDomainModelReader;
use mail\spam\domain\SpamDomainValidationRule;
use spitfire\core\http\URL;
use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
use spitfire\io\Upload;
use spitfire\validation\rules\FilterValidationRule;
use spitfire\validation\rules\MaxLengthValidationRule;
use spitfire\validation\rules\MinLengthValidationRule;
use spitfire\validation\ValidationError;
use spitfire\validation\ValidationException;

class EditController extends BaseController
{
	
	public function username() {
		if (!$this->user) { throw new PublicException('You need to be logged in', 401); }
		
		if (null != $s = $this->user->isSuspended()) {
			throw new PublicException('Account has been limited. Reason given: ' . $s->reason);
		}
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException(); }
			
			#Read the username if it was sent, check if it's valid
			$username = _def($_POST['username'], '');
			if (!preg_match('/^[a-zA-Z][a-zA-Z0-9\-\_]{2,19}$/', $username)) { 
				throw new ValidationException('Invalid username', 400, Array(new ValidationError('Username is invalid', 'Usernames may not start with a number and contain only letters, numbers, hyphens and underscores'))); 
			}
			
			#Check if the new username is taken
			$dupquery = db()->table('username')->get('name', $username)
					->group()
						->addRestriction('expires', null, 'IS')
						->addRestriction('expires', time(), '>')
					->endGroup();
			
			$taken = $dupquery->fetch();
			/*
			 * Check if the username was already taken / is still locked by a user
			 * that is not the current one.
			 */
			if ($taken && $taken->user->_id === $this->user->_id) {/*Do nothing*/}
			elseif ($dupquery->count() !== 0) { throw new ValidationException('Username is taken', 400, Array(new ValidationError('Username is taken', 'Please select a different username'))); }
			
			#In case the user is moving back to a previous alias, we will let him do so
			$new = $taken !== null? $taken : db()->table('username')->newRecord();
			
			#Go on, now setting the old username as past
			$old = $this->user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch();
			
			#If a user accidentally attempts to use the same username as they 
			#already are, we stop them from doing so.
			if ($old->name === $new->name) {
				throw new ValidationException('Username is already' . htmlspecialchars($username), 400, Array(new ValidationError('Pick a different username', 'To change your username enter a different one')));
			}
			
			#Set the old username as expired in 3 months
			$old->expires = time() + (90 * 24 * 3600);
			$old->store();
			
			$new->user = $this->user;
			$new->name = $username;
			$new->expires = null;
			$new->store();
			
			#Notify the webhook about the change
			$this->hook && $this->hook->trigger('user.update', ['type' => 'user', 'id' => $this->user->_id]);
			
			return $this->response->getHeaders()->redirect(url());
		} 
		catch (HTTPMethodException$e) {/*Do nothing here*/}
		catch (ValidationException$e) { $this->view->set('messages', $e->getResult());}
	}
	
	public function email() {
		if (!$this->user) { throw new PublicException('Need to be logged in', 403); }
		
		if (null != $s = $this->user->isSuspended()) {
			throw new PublicException('Account has been limited. Reason given: ' . $s->reason);
		}
		
		if (!$this->level->count()) {
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url('auth', 'add', 1, ['returnto' => strval(URL::current())]));
			return;
		}
		
		if ($this->request->isPost()) {
			#Read the email from Post
			$email = _def($_POST['email'], '');
			
			#Check if the email is actually an email
			$v = validate()->addRule(new FilterValidationRule(FILTER_VALIDATE_EMAIL, 'Invalid email'));
			$v->addRule(new MaxLengthValidationRule(50, 'Email cannot be longer than 50 characters'));
			$v->addRule(new SpamDomainValidationRule(new SpamDomainModelReader(db())));
			validate($v->setValue($email));
			
			#Check if the email is currently in use
			if (db()->table('user')->get('email', $email)->count() !== 0) {
				throw new PublicException('Email is duplicated', 400);
			}
			
			#Store the new email and de-verify the account.
			$this->user->email = $email;
			$this->user->verified = false;
			$this->user->store();
			
			return $this->response->setBody('redirecting...')->getHeaders()->redirect(url());
		}
		
	}
	
	public function password() {
		
		if (!$this->user) { throw new PublicException('Need to be logged in', 403); }
		
		if ($this->level->count() < 1) {
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url('auth', 'add', 1, ['returnto' => strval(URL::current())]));
			return;
		}
		
		if ($this->request->isPost()) {
			#Read the email from Post
			$passNew = _def($_POST['password'], '');
			
			#Check if the email is actually an email
			$v = validate()->addRule(new MinLengthValidationRule(8, 'Your password needs to be at least 8 characters'));
			validate($v->setValue($passNew));
			
			#Store the new email and de-verify the account.
			$oldpass = db()->table('authentication\provider')->get('user', $this->user)->where('type', \authentication\ProviderModel::TYPE_PASSWORD)->where('expires', null)->first();
			$oldpass->expires = time();
			$oldpass->store();
			
			$newpass = db()->table('authentication\provider')->newRecord();
			$newpass->user = $this->user;
			$newpass->type = \authentication\ProviderModel::TYPE_PASSWORD;
			$newpass->content = password_hash($_POST['password'], PASSWORD_DEFAULT);
			$newpass->expires = null;
			$newpass->store();
			
			#TODO: Notify the user that the password was changed
			
			
			#Notify the webhook about the change
			$this->hook && $this->hook->trigger('user.update', ['type' => 'user', 'id' => $this->user->_id]);
			
			return $this->response->getHeaders()->redirect(url('edit', 'password', ['result' => 'success']));
		}
	}
	
	public function avatar() {
		
		if (!$this->user) { throw new PublicException('Need to be logged in', 403); }
		
		if ($this->request->isPost() && $_POST['upload'] instanceof Upload) {
			$upload = $_POST['upload'];
			$this->user->picture = $upload->validate()->store()->uri();
			$this->user->store();
			
			#Notify the webhook about the change
			$this->hook && $this->hook->trigger('user.update', ['type' => 'user', 'id' => $this->user->_id]);
			
			return $this->response->getHeaders()->redirect(url());
		}
	}
	
}
