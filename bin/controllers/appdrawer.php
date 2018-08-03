<?php

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

class AppdrawerController extends BaseController
{
	
	public function index() {
		
		if (isset($_GET['all']) && !$this->authapp) {
			throw new PublicException('App needs to be authenticated for full listing', 403);
		}
		
		$q = db()->table('authapp')->getAll();
		
		/*
		 * Check if the app is just requiring to show the apps from the drawer,
		 * otherwise we will show the application all the apps it can access from
		 * within it's scope (non-system apps do not have access to the system app list)
		 */
		if (!isset($_GET['all'])) {
			$q->where('drawer', true);
		}
		elseif (!$this->authapp->system) {
			$q->where('system', false);
		}
		
		$this->view->set('apps', $q->all());
	}
	
}