<?php

use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
use spitfire\validation\ValidationException;
use webhook\HookModel;

class WebhookController extends BaseController
{
	
	/**
	 * 
	 * @validate >> POST#type(required in[user, token, app, group])
	 * @validate >> POST#action(required in[create, update, delete, member])
	 * @validate >> POST#url(required url)
	 * @validate >> POST#id (required string)
	 * 
	 * @param AuthAppModel $app
	 * @throws PublicException
	 * @throws HTTPMethodException
	 */
	public function attach(AuthAppModel$app) {
		#Check the user's privileges
		if (!$this->isAdmin) { throw new PublicException('Requires authorization', 403); }
		
		try {
			
			#If the request was posted, then the user can store the hook
			if (!$this->request->isPost()) { throw new HTTPMethodException('Needs to be posted', 1709131431); }
			
			if (!empty($this->validate))   { throw new ValidationException('Validation failed', 1807181015, $this->validate->toArray()); }
			
			$this->hook->authenticate();
			#Read the variables.
			$type   = $_POST['type'];
			$action = $_POST['action'];
			
			$hookserver = db()->table('authapp')->get('_id', SysSettingModel::getValue('cptn.h00k'))->first(true)->appID;
			$hook = $this->hook->register($hookserver, $app->appID, $_POST['id'], $type . '.' . $action, $_POST['url']);
			
			#Redirect to the new hook
			$this->response->getHeaders()->redirect(url('webhook', 'edit', $hook->id));
			return;
		} 
		catch (HTTPMethodException$e) {}
		catch (ValidationException$e) {
			$this->view->set('messages', $e->getResult());
		}
	}
	
	/**
	 * 
	 * @validate >> POST#url(string url required) AND POST#type(number)
	 * 
	 * @param type $hookid
	 * @return type
	 * @throws PublicException
	 * @throws HTTPMethodException
	 */
	public function edit(HookModel$hook) {
		#Check the user's privileges
		if (!$this->isAdmin) { throw new PublicException('Requires authorization', 403); }
		
		
		try {
			
			#If the request was posted, then the user can store the hook
			if (!$this->request->isPost()) { throw new HTTPMethodException('Needs to be posted', 1709131431); }
			if (!current_context()->validation->isEmpty()) { throw new ValidationException('Validation failed', 0, current_context()->validation->toArray()); }
			
			#Read the variables.
			$type   = (int)$_POST['type'];
			$action = (int)$_POST['action'];
			
			#Validate the hook
			$validators = [];
			$validators['url']    = validate($_POST['url'])->asURL('URL needs to be valid');
			
			$validators['type']   = validate($type)->addRule(new ClosureValidationRule(function ($v) {
				return array_reduce(
					[HookModel::APP & $v, HookModel::USER & $v, HookModel::TOKEN & $v, HookModel::GROUP & $v], 
					function ($e, $p) { return $e? $p + 1 : $p; }, 0 
				) === 1? false : 'Type error';
			}));
			
			$validators['action']  = validate($action)->addRule(new ClosureValidationRule(function ($v) {
				return array_reduce(
					[HookModel::CREATED & $v, HookModel::UPDATED & $v, HookModel::DELETED & $v, HookModel::MEMBER & $v], 
					function ($e, $p) { return $e? $p + 1 : $p; }, 0 
				) === 1? false : 'Type error';
			}));
			
			$validators['listen'] = validate($type & $action)->addRule(new ClosureValidationRule(function ($v) {
				return (!($v & HookModel::MEMBER) || $v & HookModel::APP)? false : 'Type error';
			}));
			
			validate($validators);
			
			#Store the hook
			$hook->name   = _def($_POST['name'], '');
			$hook->listen = $validators['listen']->getValue();
			$hook->url    = $validators['url']->getValue();
			$hook->store();
			
			#Redirect to the new hook
			$this->response->getHeaders()->redirect(url('webhook', 'edit', $hook->_id));
			return;
		} 
		catch (HTTPMethodException$e) {}
		catch (ValidationException$e) {
			$this->view->set('messages', $e->getResult());
		}
		
		$this->view->set('webhook', $hook);
	}
	
	public function delete($webhookid, $hash = null) {
		
		$hook = db()->table('webhook\hook')->get('_id', $webhookid)->fetch();
		$app  = $hook->app;
		
		if (!$hook) { throw new PublicException('No hook found', 404); }
		
		$expected = sha1($hook->_id . $hook->url . date('Y-m-d'));
		
		if ($hash === $expected) {
			$hook->delete();
			
			$this->response->getHeaders()->redirect(url('app', 'detail', $app->_id));
			return;
		}
		
		$this->view->set('confirmURL', url('webhook', 'delete', $hook->_id, $expected));
		$this->view->set('cancelURL', url('app', 'detail', $app->_id));
	}
	
}

