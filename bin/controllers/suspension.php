<?php

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
			throw new \spitfire\exceptions\PrivateException('You cannot acces this section without being an admin', 403); 
		}
	}
	
	public function create($userid) {
		
		$user = db()->table('user')->get('_id', $userid)->fetch();
		if (!$user) { throw new \spitfire\exceptions\PublicException('No user found', 404); }
		
		switch(_def($_POST['duration'], '0h')) {
			case '6h': $duration =   6 *  3600; break;
			case '1d': $duration =       86400; break;
			case '3d': $duration =   3 * 86400; break;
			case '1w': $duration =   7 * 86400; break;
			case '2w': $duration =  14 * 86400; break;
			case '1m': $duration =  30 * 86400; break;
			case '3m': $duration =  90 * 86400; break;
			case '6m': $duration = 180 * 86400; break;
			case '1y': $duration = 360 * 86400; break;
			default  : $duration = (int)$_POST['duration'];
		}
		
		$blockLogin = _def($_POST['blockLogin'], 'n') === 'y';
		
		$ban = db()->table('user\suspension')->newRecord();
		$ban->user   = $user;
		$ban->expires = time() + $duration;
		$ban->preventLogin = $blockLogin;
		$ban->reason = _def($_POST['reason'], '');
		$ban->notes  = _def($_POST['notes'], '');
		$ban->store();
		
		$this->response->getHeaders()->redirect(url('user', 'detail', $user->_id));
		
	}
	
}