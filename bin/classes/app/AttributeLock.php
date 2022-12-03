<?php namespace app;

use AttributeModel;
use AuthAppModel;
use UserModel;

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

class AttributeLock
{
	
	const MODE_N = 0x00;
	const MODE_R = 0x10;
	const MODE_W = 0x20;
	const MODE_RW = 0x30;
	
	/**
	 *
	 * @var \AuthAppModel
	 */
	private $scope;
	
	private $user;
	
	public function __construct(AttributeModel$scope, UserModel$user = null) {
		$this->scopes   = $scope;
		$this->user    = $user;
	}
	
	public function unlock(AuthAppModel$app, $mode = self::MODE_R) {
		$db = $this->scopes->getTable()->getDb();
		$q  = $db->table('attribute\appgrant')->getAll();
		
		$q->where('attribute', $this->scope);
		$q->where('app', $app);
		
		$q->group()->where('user', $this->user)->where('user', null);
		
		$grant = $q->all()->reduce(function ($carry, $i) use ($mode) { 
			/*
			 * The user setting will override any previously set state. App based 
			 * rules will override the standard setting
			 */
			return $carry === null || $i->user? ((int)$i->grant) & $mode : $carry;
		}, null);
		
		return $grant !== null? !!$grant : $this->def($mode);
	}
	
	public function def($mode) {
		switch($mode) {
			case self::MODE_R:
				return $this->scopes->readable === 'public';
			case self::MODE_W:
				return $this->scopes->writable === 'public';
			case self::MODE_R | self::MODE_W:
				return $this->scopes->readable === 'public' && $this->context->writable === 'public';
		}
	}
	
}
