<?php

use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
use spitfire\io\Image;
use spitfire\io\Upload;
use spitfire\validation\EmptyValidationRule;
use spitfire\validation\FilterValidationRule;
use spitfire\validation\MinLengthValidationRule;
use spitfire\validation\ValidationException;

class EditController extends BaseController
{
	
	public function username() {
		if (!$this->user) { throw new PublicException('You need to be logged in', 401); }
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException(); }
			
			#Read the username if it was sent, check if it's valid
			$username = _def($_POST['username'], '');
			if (!preg_match('/^[a-zA-z][a-zA-z0-9\-\_]{2,19}$/', $username)) { throw new ValidationException('Invalid username', 400, Array('Username is invalid')); }
			
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
			elseif ($dupquery->count() !== 0) { throw new ValidationException('Username is taken', 400, Array('Username is taken')); }
			
			#In case the user is moving back to a previous alias, we will let him do so
			$new = $taken !== null? $taken : db()->table('username')->newRecord();
			
			#Go on, now setting the old username as past
			$old = $this->user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch();
			
			#If a user accidentally attempts to use the same username as they 
			#already are, we stop them from doing so.
			if ($old->name === $new->name) {
				throw new PublicException('Username is already ' . htmlspecialchars($username), 400);
			}
			
			#Set the old username as expired in 3 months
			$old->expires = time() + (90 * 24 * 3600);
			$old->store();
			
			$new->user = $this->user;
			$new->name = $username;
			$new->expires = null;
			$new->store();
			
			return $this->response->getHeaders()->redirect(new URL());
		} 
		catch (HTTPMethodException$e) {/*Do nothing here*/}
		catch (ValidationException$e) { $this->view->set('messages', $e->getResult());}
	}
	
	public function email() {
		if (!$this->user) { throw new PublicException('Need to be logged in', 403); }
		
		if ($this->request->isPost()) {
			#Read the email from Post
			$email = _def($_POST['email'], '');
			
			#Check if the email is actually an email
			$v = validate()->addRule(new FilterValidationRule(FILTER_VALIDATE_EMAIL, 'Invalid email'));
			validate($v->setValue($email));
			
			#Check if the email is currently in use
			if (db()->table('user')->get('email', $email)->count() !== 0) {
				throw new PublicException('Email is duplicated');
			}
			
			#Store the new email and de-verify the account.
			$this->user->email = $email;
			$this->user->verified = false;
			$this->user->store();
			
			return $this->response->getHeaders()->redirect(new URL());
		}
		
	}
	
	public function password() {
		
		if (!$this->user) { throw new PublicException('Need to be logged in', 403); }
		
		if ($this->request->isPost()) {
			#Read the email from Post
			$passNew = _def($_POST['password'], '');
			$passVer = _def($_POST['password_verify'], '');
			$passOld = _def($_POST['password_old'], '');
			
			#Check if the email is actually an email
			$v = validate()->addRule(new MinLengthValidationRule(8, 'Your password needs to be at least 8 characters'));
			validate($v->setValue($passNew));
			
			#Check if the verification and Password match
			if ($passNew !== $passVer) { throw new PublicException('Passwords do not match', 400); }
			
			#Check if the old password is correct
			if (!$this->user->checkPassword($passOld)) {
				throw new PublicException('Old password is incorrect');
			}
			
			#Store the new email and de-verify the account.
			$this->user->setPassword($passNew);
			$this->user->store();
			
			return $this->response->getHeaders()->redirect(new URL());
		}
	}
	
	public function avatar() {
		
		if (!$this->user) { throw new PublicException('Need to be logged in', 403); }
		
		if ($this->request->isPost() && $_POST['upload'] instanceof Upload) {
			$upload = $_POST['upload'];
			$this->user->picture = $upload->validate()->store();
			$this->user->store();
			
			return $this->response->getHeaders()->redirect(new URL());
		}
	}
	
	public function attribute($attrid) {
		
		if (!$this->user) { throw new PublicException('Need to be logged in', 403); }
		
		/*
		 * Check if the attribute exists and is writable. This should prevent users
		 * from causing vandalism on the site.
		 */
		$attribute = db()->table('attribute')->get('_id', $attrid)->fetch();
		if (!$attribute || $attribute->writable === 'nem') { throw new Exception('No property found', 404); }
		
		$attributeValue = db()->table('user\attribute')->get('user', $this->user)->addRestriction('attr', $attribute)->fetch();
		
		if ($this->request->isPost()) {
			
			/*
			 * It may happen that this user never defined this attribute, in this 
			 * case, we're creating it.
			 */
			if ($attributeValue === null) { 
				$attributeValue = db()->table('user\attribute')->newRecord(); 
				$attributeValue->user = $this->user;
				$attributeValue->attr = $attribute;
			}
		
			$v = validate();
			if ($attribute->required) { $v->addRule(new EmptyValidationRule('Value cannot be empty')); }
			
			/*
			 * Depending on the data type that we're receiving we need to handle
			 * the data differently. Since by spec, HTML files and checkboxes are
			 * transmitted differently we need to account for those separately
			 */
			if ($attribute->datatype === 'file') { 
				$value = isset($_POST['value']) && $_POST['value'] instanceof Upload? $_POST['value'] : null; 
			}
			elseif ($attribute->datatype === 'boolean') { $value = isset($_POST['value'])? 1 : 0; }
			else { $value = _def($_POST['value'], ''); }
			
			$validators = db()->table('attribute\validator')->get('attribute', $attribute)->fetchAll();
			foreach ($validators as $dbValidator) {
				$vname = $dbValidator->validator;
				$rule  = new $vname();
				$rule->load($dbValidator->settings);
				$v->addRule($rule);
			}
			
			#Validate the new value
			validate($v->setValue($value));
			
			$attributeValue->value = $value instanceof Upload? $value->validate()->store() : $value;
			$attributeValue->store();
			
			return $this->response->getHeaders()->redirect(new URL());
		}
		
		$this->view->set('attribute', $attribute);
		$this->view->set('value', $attributeValue? $attributeValue->value : '');
	}
	
}
