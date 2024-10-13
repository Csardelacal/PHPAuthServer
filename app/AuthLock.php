<?php namespace magic3w\phpauth;

use UserModel;
use AuthAppModel;
use connection\AuthModel;

/*
 * The MIT License
 *
 * Copyright 2018 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class AuthLock
{
	
	/**
	 *
	 * @var \AuthAppModel
	 */
	private $scope;
	
	private $user;
	
	private $context;
	
	public function __construct(AuthAppModel$scope, UserModel$user, $context)
	{
		$this->scopes   = $scope;
		$this->user    = $user;
		$this->context = $context;
	}
	
	public function unlock(AuthAppModel$app)
	{
		$db = $this->scopes->getTable()->getDb();
		$q  = $db->table('connection\auth')->getAll();
		
		$q->where('target', $this->scope);
		$q->where('source', $app);
		
		if ($this->context) {
			$q->where('context', $this->context);
		}
		
		$q->group()->where('user', $this->user)->where('user', null);
		$q->group()->where('expires', null)->where('expires', '>', time());
		
		return $q->all()->reduce(function ($c, $i) {
			/*
			 * The user setting will override any previously set state. App based
			 * rules will override the standard PENDING setting
			 */
			return $c === null || $i->user? (int)$i->state : $c;
		}, null)? : AuthModel::STATE_PENDING;
	}
}
