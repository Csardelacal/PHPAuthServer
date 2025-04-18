<?php

use mail\spam\domain\implementation\SpamDomainModelReader;
use mail\spam\domain\SpamDomainValidationRule;
use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
use spitfire\io\Upload;
use spitfire\validation\EmptyValidationRule;
use spitfire\validation\rules\FilterValidationRule;
use spitfire\validation\rules\MaxLengthValidationRule;
use spitfire\validation\rules\MinLengthValidationRule;
use spitfire\validation\ValidationError;
use spitfire\validation\ValidationException;

class EditController extends BaseController
{
	
	public function username()
	{
		if (!$this->user) {
			throw new PublicException('You need to be logged in', 401);
		}
		
		if (null != $s = $this->user->isSuspended()) {
			throw new PublicException('Account has been limited. Reason given: ' . $s->reason);
		}
		
		try {
			if (!$this->request->isPost()) {
				throw new HTTPMethodException();
			}
			
			#Read the username if it was sent, check if it's valid
			$username = _def($_POST['username'], '');
			if (!preg_match('/^[a-zA-Z][a-zA-Z0-9\-\_]{2,19}$/', $username)) {
				throw new ValidationException(
					'Invalid username',
					400,
					[
						new ValidationError(
							'Username is invalid',
							'Username may not start with a number and contain letters, numbers, hyphens and underscores'
						)
					]
				);
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
			if ($taken && $taken->user->_id === $this->user->_id) {
				/*Do nothing*/
			}
			elseif ($dupquery->count() !== 0) {
				throw new ValidationException(
					'Username is taken',
					400,
					[new ValidationError('Username is taken', 'Please select a different username')]
				);
			}
			
			#In case the user is moving back to a previous alias, we will let him do so
			$new = $taken !== null? $taken : db()->table('username')->newRecord();
			
			#Go on, now setting the old username as past
			$old = $this->user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch();
			
			#If a user accidentally attempts to use the same username as they
			#already are, we stop them from doing so.
			if ($old->name === $new->name) {
				throw new ValidationException(
					'Username is already' . htmlspecialchars($username),
					400,
					[new ValidationError('Pick a different username', 'To change your username enter a different one')]
				);
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
		catch (HTTPMethodException$e) {
			/*Do nothing here*/
		}
		catch (ValidationException$e) {
			$this->view->set('messages', $e->getResult());
		}
	}
	
	public function email()
	{
		if (!$this->user) {
			return
			$this->response->setBody('Redirecting')
					->getHeaders()->redirect(url('user', 'login', ['returnto' => (string)url('edit', 'email')]));
		}
		
		if (null != $s = $this->user->isSuspended()) {
			throw new PublicException('Account has been limited. Reason given: ' . $s->reason);
		}
		
		if ($this->request->isPost() && $this->user->checkPassword($_POST['password'])) {
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
	
	public function password()
	{
		
		if (!$this->user) {
			throw new PublicException('Need to be logged in', 403);
		}
		
		if ($this->request->isPost()) {
			#Read the email from Post
			$passNew = _def($_POST['password'], '');
			$passVer = _def($_POST['password_verify'], '');
			$passOld = _def($_POST['password_old'], '');
			
			#Check if the email is actually an email
			$v = validate()->addRule(new MinLengthValidationRule(8, 'Your password needs to be at least 8 characters'));
			validate($v->setValue($passNew));
			
			#Check if the verification and Password match
			if ($passNew !== $passVer) {
				throw new PublicException('Passwords do not match', 400);
			}
			
			#Check if the old password is correct
			if (!$this->user->checkPassword($passOld)) {
				throw new PublicException('Old password is incorrect', 401);
			}
			
			#Store the new email and de-verify the account.
			$this->user->setPassword($passNew);
			$this->user->store();
			
			#Notify the webhook about the change
			$this->hook && $this->hook->trigger('user.update', ['type' => 'user', 'id' => $this->user->_id]);
			
			return $this->response->getHeaders()->redirect(url());
		}
	}
	
	public function avatar()
	{
		
		if (!$this->user) {
			throw new PublicException('Need to be logged in', 403);
		}
		
		if ($this->request->isPost() && $_POST['upload'] instanceof Upload) {
			$previous = $this->user->picture;
			
			$upload = $_POST['upload'];
			$this->user->picture = $upload->validate()->store()->uri();
			$this->user->store();
			
			/**
			 * If the user already had an avatar, the application should delete the old one
			 * to clean up after itself.
			 *
			 * Eventually this should use figure for this, eliminating the risk that a file
			 * could be lost if it is deleted prematurely. Right now, the moment the file is
			 * deleted it will be permanently removed.
			 */
			try {
				$previous && storage($previous)->delete();
			}
			catch (Exception $e) {
			}
			
			#Notify the webhook about the change
			$this->hook && $this->hook->trigger('user.update', ['type' => 'user', 'id' => $this->user->_id]);
			
			return $this->response->getHeaders()->redirect(url());
		}
	}
	
	public function attribute($attrid)
	{
		
		if (!$this->user) {
			throw new PublicException('Need to be logged in', 403);
		}
		
		if ($this->token && !$this->authapp) {
			throw new PublicException('Insufficient privileges. Token context requires app context', 403);
		}
		
		/*
		 * Check if the attribute exists. Prepare a lock for the attribute, the lock
		 * is a helper class that allows to check whether certain applications have
		 * sufficient privileges to edit this attribute.
		 */
		$attribute = db()->table('attribute')->get('_id', $attrid)->fetch();
		
		if (!$attribute) {
			throw new Exception('No property found', 404);
		}
		
		/*
		 * If an application is requesting write privileges, we check whether the
		 * application can access the data. If it does not have the necessary
		 * privileges, we can stop it right there.
		 */
		if ($this->authapp) {
			throw new PublicException('No write permission', 403);
		}
		/*
		 * If the user is trying to write the data we confirm that the user is not
		 * being prevented from doing so by virtue of the value being a system variable.
		 */
		elseif ($attribute->writable === 'nem') {
			throw new Exception('System property. This value is not user configurable.', 404);
		}
		
		/*
		 * Get the value for the attribute.
		 */
		$attributeValue = db()->table(user\AttributeModel::class)->get('user', $this->user)->where('attr', $attribute)->first();
		
		/*
		 * Fetch the validators the system has for the value. This way we can check
		 * the data submitted and can also inform the user of errors.
		 */
		$validators = db()->table(attribute\ValidatorModel::class)->get('attribute', $attribute)->fetchAll();
		
		try {
			if (!$this->request->isPost()) {
				throw new HTTPMethodException();
			}
			
			/*
			 * It may happen that this user never defined this attribute, in this
			 * case, we're creating it.
			 */
			if ($attributeValue === null) {
				$attributeValue = db()->table(user\AttributeModel::class)->newRecord();
				$attributeValue->user = $this->user;
				$attributeValue->attr = $attribute;
			}
			
			$v = validate();
			if ($attribute->required) {
				$v->addRule(new EmptyValidationRule('Value cannot be empty'));
			}
			
			/*
			 * Depending on the data type that we're receiving we need to handle
			 * the data differently. Since by spec, HTML files and checkboxes are
			 * transmitted differently we need to account for those separately
			 */
			if ($attribute->datatype === 'file') {
				$value = isset($_POST['value']) && $_POST['value'] instanceof Upload? $_POST['value'] : null;
			}
			elseif ($attribute->datatype === 'boolean') {
				$value = isset($_POST['value'])? 1 : 0;
			}
			else {
				$value = _def($_POST['value'], '');
			}
			
			foreach ($validators as $dbValidator) {
				$vname = $dbValidator->validator;
				$rule  = new $vname();
				$rule->load($dbValidator->settings);
				$v->addRule($rule);
			}
			
			#Validate the new value
			validate($v->setValue($value));
			
			/**
			 * Extract the old value from the database, so once it's overridden, the data can be safely
			 * deleted from the drive.
			 */
			$previous = $attributeValue->value;
			
			$attributeValue->value = $value instanceof Upload? $value->validate()->store()->uri() : $value;
			$attributeValue->store();
			
			/**
			 * If the user already had the given attribute, the application should delete the old one
			 * to clean up after itself.
			 *
			 * The attribute system is getting deprecated in future revisions, please make sure to use
			 * this sparingly.
			 */
			try {
				$previous && storage($previous)->delete();
			}
			catch (Exception $e) {
			}
			
			#Notify the webhook about the change
			$this->hook && $this->hook->trigger('user.update', ['type' => 'user', 'id' => $this->user->_id]);
			
			return $this->response->setBody('Redirect...')->getHeaders()->redirect(url());
		}
		catch (HTTPMethodException$e) {
/* Do nothing, show the form normall */
		}
		catch (ValidationException$e) {
			$this->view->set('errors', $e->getResult());
		}
		
		$grants = db()->table('authapp')->get('system', false)->all();
		
		$this->view->set('apps', $grants);
		$this->view->set('attribute', $attribute);
		$this->view->set('value', $attributeValue? $attributeValue->value : '');
	}
}
