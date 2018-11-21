<?php

use spitfire\exceptions\PrivateException;
use spitfire\exceptions\PublicException;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SuspensionController extends AppController
{
	
	
	public function _onload() {
		parent::_onload();
		
		if (!$this->isAdmin) { 
			throw new PrivateException('You cannot acces this section without being an admin', 403); 
		}
	}
	
	public function create($userid) {
		
		$user = db()->table('user')->get('_id', $userid)->fetch();
		if (!$user) { throw new PublicException('No user found', 404); }
		
		switch(_def($_POST['duration'], '0h')) {
			case '6h' : $duration =    6 *  3600; break;
			case '12h': $duration =   12 *  3600; break;
			case '1d' : $duration =        86400; break;
			case '3d' : $duration =    3 * 86400; break;
			case '1w' : $duration =    7 * 86400; break;
			case '2w' : $duration =   14 * 86400; break;
			case '1m' : $duration =   30 * 86400; break;
			case '3m' : $duration =   90 * 86400; break;
			case '6m' : $duration =  180 * 86400; break;
			case '1y' : $duration =  365 * 86400; break;
			case '10y': $duration = 3650 * 86400; break;
			default   : $duration = (int)$_POST['duration'];
		}
		
		$blockLogin = _def($_POST['blockLogin'], 'n') === 'y';
		
		$ban = db()->table('user\suspension')->newRecord();
		$ban->user   = $user;
		$ban->expires = time() + $duration;
		$ban->preventLogin = $blockLogin;
		$ban->reason = _def($_POST['reason'], '');
		$ban->notes  = _def($_POST['notes'], '');
		$ban->store();
		
		/*
		 * Retrieve a list of tokens for the current user. If the user was banned
		 * (blocking log-in) then we disable their current tokens.
		 */
		$tokens = db()->table('token')->get('user', $user)->addRestriction('expires', time(), '>')->fetchAll();
		
		foreach ($tokens as $token) {
			/*
			 * All of the user's tokens are expired, forcing them to log back into 
			 * the application.
			 */
			$token->expires = time() - 1;
			$token->store();
			
			/*
			 * Notify the webhook server that the token was deleted. Applications
			 * may need to empty their caches to prevent the user from continuing 
			 * to use them.
			 */
			$this->hook && $this->hook->trigger('token.expire', ['token' => $token->token, 'user' => $user->_id]);
		}
		
		/*
		 * Some applications also perform user profile level caching. Something has
		 * changed for this user, so we inform the application about it too.
		 */
		$this->hook && $this->hook->trigger('user.update', ['type' => 'user', 'id' => $this->user->_id]);
		
		/*
		 * The user is now suspended, we can redirect to the profile.
		 */
		$this->response->getHeaders()->redirect(url('user', 'detail', $user->_id));
		
	}
	
	public function end(\user\SuspensionModel$s) {
		
		if (!$this->isAdmin || $this->token || $this->authapp) {
			throw new PublicException('Invalid context', 403);
		}
		
		$s->expires = time();
		$s->store();
		
		return $this->response->setBody('Redirecting')->getHeaders()->redirect(url('user', 'detail', $s->user->_id));
	}
	
}