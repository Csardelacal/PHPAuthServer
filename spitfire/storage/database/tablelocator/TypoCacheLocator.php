<?php namespace spitfire\storage\database\tablelocator;

use Closure;
use spitfire\cache\MemoryCache;

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
 * This locator allows the application to find a file in the cache by applying a 
 * provided function that will correct common typos.
 * 
 * Usually, when locating tables, the user may provide "users" for the "user" 
 * model. While this is obvious to a human, the machine would generally fail to
 * find the correct model / table.
 * 
 * To prevent the system being annoying to the developer, we issue a notice that
 * will show up in the log when debugging is enabled but will work properly while
 * the user is still prototyping their application.
 */
class TypoCacheLocator extends CacheLocator
{
	
	/**
	 * The function provided to the system to correct typos. The function needs 
	 * to accept one parameter (string) and return the corrected version of the 
	 * string.
	 *
	 * @var Closure
	 */
	private $fn;
	
	/**
	 * Creates a new typo correcting table cache locator. To construct this object
	 * we provide a cache to search and function to correct the table name that
	 * the locate function later needs to find a table.
	 * 
	 * @param MemoryCache $cache
	 * @param Closure $fn
	 */
	public function __construct(MemoryCache$cache, $fn) {
		$this->fn = $fn;
		parent::__construct($cache);
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * This method corrects the name of the table to find potentially typo-ed 
	 * table names.
	 * 
	 * @param string $tablename
	 * @return \spitfire\storage\database\Table|false
	 */
	public function locate(string $tablename) {
		$located = parent::locate($this->fn($tablename));
		
		if ($located) {
			trigger_error(
				sprintf('Table %s was misspelled. Prefer %s instead', $tablename, $this->fn($tablename)), 
				E_USER_NOTICE);
		}
		
		return $located;
	}
}