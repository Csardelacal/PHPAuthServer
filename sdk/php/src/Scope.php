<?php namespace magic3w\phpauth\sdk;

use spitfire\io\request\Request;

/*
 * The MIT License
 *
 * Copyright 2017 César de la Cal Bretschneider <cesar@magic3w.com>.
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

/**
 * The scope of an application defines a namespace within it, which allows the applications
 * to request granular control instead of demanding blanket access to the data.
 *
 * For example, when the application whishes to log the user into it, it just needs access
 * to the user's basic data, there's no need to access (for example) the user's shopping history.
 */
class Scope
{
	
	/**
	 * The application that contains the scope.
	 *
	 * @var App
	 */
	private $app;
	
	/**
	 * The name of the scope. This is unique within an application.
	 *
	 * @var string
	 */
	private $id;
	
	/**
	 *
	 * @param App    $app
	 * @param string $id
	 */
	public function __construct(App $app, string $id)
	{
		$this->app = $app;
		$this->id = $id;
	}
	
	/**
	 *
	 * @return App
	 */
	public function getApp() : App
	{
		return $this->app;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getId() : string
	{
		return $this->id;
	}
	
	/**
	 *
	 * @param App $app
	 * @return Scope
	 */
	public function setApp(App $app) : Scope
	{
		$this->app = $app;
		return $this;
	}
	
	/**
	 *
	 * @param string $name
	 * @return Scope
	 */
	public function setId(string $name) : Scope
	{
		$this->id = $name;
		return $this;
	}
}
