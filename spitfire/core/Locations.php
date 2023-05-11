<?php namespace spitfire\core;

use spitfire\contracts\core\LocationsInterface;

/*
 * The MIT License
 *
 * Copyright 2020 cesar.
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
 * This class provides utility access to most of Spitfire's most common paths,
 * including the current working directory, the root directory of spitfire, the
 * vendor directory and the different locations of each application that is active.
 *
 * All directories must be returned with a trailing slash to ensure consistent
 * behavior across different apps.
 */
class Locations implements LocationsInterface
{
	
	private $basedir;
	
	public function __construct(string $basedir)
	{
		$this->basedir = $basedir;
	}
	
	/**
	 * Returns the current working directory for the application. This will determine
	 * how the system looks up files.
	 *
	 * The working directory is not always the directory that contains the files
	 * of the application. Here are two examples:
	 *
	 * - `php console ...` // Working directory == application root
	 * - `cd /files; php /myapp/console ...` // Working directory =/= application root
	 *
	 * Whenever working with the application's file you should use root(). When
	 * letting the user define a set of files to work with or a directory to write
	 * to, you should use the working directory.
	 *
	 * @param string $path A path to locate within the folder [optional]
	 * @return string
	 */
	public function working($path = '') : string
	{
		return rtrim(getcwd()?: './', '\/') . DIRECTORY_SEPARATOR . ltrim($path, '\/');
	}
	
	/**
	 * Returns the path of the root directory of spitfire.
	 *
	 * @param string $path  [optional]
	 * @return string
	 */
	public function root(string $path = '') : string
	{
		return $this->basedir . DIRECTORY_SEPARATOR . ltrim($path, '\/');
	}
	
	/**
	 * The config path should contain configuration files that the system can load
	 * to make decisions based on the preferences the operator has determined.
	 *
	 * @param string $path A path to locate within the folder [optional]
	 * @return string
	 */
	public function config(string $path = '') : string
	{
		return $this->root('config/' . ltrim($path, '\/'));
	}
	
	/**
	 * Returns the location of the spitfire installation. Most spitfire
	 * installations do not need to have access to the location of the framework.
	 *
	 * @param string $path A path to locate within the folder [optional]
	 * @return string The location of the spitfire installation
	 */
	public function spitfire(string $path = '') : string
	{
		$sfdir = dirname(__DIR__);
		return rtrim($sfdir, '\/') . DIRECTORY_SEPARATOR . ($path? ltrim($path, '\/') : '');
	}
	
	/**
	 * Returns a path to the public folder (or a selected path within it). Files
	 * in this location can be served by the web-server.
	 *
	 * @todo Rename to webroot?
	 * @param string $path
	 * @return string
	 */
	public function public(string $path = '') : string
	{
		return $this->root('public/' . ltrim($path, '\/'));
	}
	
	/**
	 * The resources folder contains uncompiled assets, templates, locales, etc
	 * that the system can use to enrich the experience of the user.
	 *
	 * @param string $path
	 * @return string
	 */
	public function resources(string $path = '') : string
	{
		return $this->root('resources/' . ltrim($path, '\/'));
	}
	
	/**
	 * The storage folder allows the application to determine where it should place
	 * files within the server. Files placed here are not accessible by the users without
	 * code that exposes them.
	 *
	 * @param string $path
	 * @return string
	 */
	public function storage(string $path = '') : string
	{
		return $this->root('storage/private/' . ltrim($path, '\/'));
	}
	
	/**
	 * The public storage folder returns a location to storage that the system provides
	 * within the scope of the webserver's document root. This means that files placed
	 * here will be accessible by users without the need for credentials or anything similar.
	 *
	 * @param string $path
	 * @return string
	 */
	public function publicStorage(string $path = '') : string
	{
		return $this->root('storage/public/' . ltrim($path, '\/'));
	}
}
