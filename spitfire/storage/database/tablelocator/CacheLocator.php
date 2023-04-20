<?php namespace spitfire\storage\database\tablelocator;

use spitfire\cache\MemoryCache;
use spitfire\storage\database\Table;

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
 * Locates a table inside a memory cache.
 */
class CacheLocator implements TableLocatorInterface
{
	
	/**
	 * The cache to be searched for appropriate tables.
	 *
	 * @var MemoryCache
	 */
	private $cache;
	
	/**
	 * This cache allows the application to search for tables within a Memory cache,
	 * allowing for a quick retrieval of tables that have already been assembled.
	 * 
	 * While Spitfire has a very light modeling system that assembles itself very
	 * quickly, it will be faster and more consistent to retrieve the data from
	 * a cache.
	 * 
	 * The cache also allows to compare models with the much faster "===" operator
	 * since it will just check whether the object referenced is the same.
	 * 
	 * @param MemoryCache $cache
	 */
	public function __construct(MemoryCache$cache) {
		$this->cache = $cache;
	}

	/**
	 * Extracts the table from the cache (in the event of it being available) and 
	 * returns false in the event of the table not being in the cache.
	 * 
	 * @param string $tablename
	 * @return Table|false
	 */
	public function locate(string $tablename) {
		return $this->cache->get(strtolower($tablename));
	}

}