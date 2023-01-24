<?php

use spitfire\exceptions\PublicException;
use defer\tasks\EndSessionTask;
use defer\tasks\IncinerateSessionTask;

class SessionController extends BaseController
{
	
	public function end(SessionModel $session)
	{
		#Get the user model
		if (!$this->user) {
			throw new PublicException('Not logged in', 403);
		}
		
		#Check if he's an admin or the owner of the session
		if (!$this->isAdmin && !($session->user->_id === $this->user->_id)) {
			throw new PublicException('Not an admin', 401);
		}
		
		/**
		 *
		 * @todo Invalidate access tokens depending on those sessions
		 * @todo Notify applications using those tokens
		 * @todo Add a json API
		 *
		 * @todo Remove the SESSION_SAVE_PATH constant
		 */
		$path = rtrim(realpath(session_save_path()?: sys_get_temp_dir()), '\/');
		$filename = sprintf('sess_%s', substr($session->_id, 2));
		
		unlink($path . '/' . $filename);
		$session->expires = time();
		$session->store();
		
		/**
		 * Mark the session to be ended and incinerated
		 */
		$this->defer->defer(1, EndSessionTask::class, $this->session->_id);
		$this->defer->defer(3600, IncinerateSessionTask::class, $this->session->_id);
		
		$this->response->setBody('Redirect')->getHeaders()->redirect(url());
	}
}
