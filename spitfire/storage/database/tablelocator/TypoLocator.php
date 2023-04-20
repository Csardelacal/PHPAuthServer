<?php namespace spitfire\storage\database\tablelocator;

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
 * This class searches for models that contain a typo.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class TypoLocator extends NameLocator
{
	
	/**
	 * The function used to correct the misspelled table name.
	 *
	 * @var \Closure
	 */
	private $fn;
	
	/**
	 * {@inheritdoc}
	 * 
	 * This locator will try to find a table by applying a callback, which potentially
	 * corrects the name of the table.
	 * 
	 * @param \spitfire\storage\database\DB $db
	 * @param \Closure $fn
	 */
	public function __construct(\spitfire\storage\database\DB$db, $fn) {
		$this->fn = $fn;
		parent::__construct($db);
	}
	
	/**
	 * Applies the provided function to the name of the table before checking if
	 * a model with the provided name exists.
	 * 
	 * @param string $tablename
	 * @return \spitfire\storage\database\Table
	 */
	public function locate(string $tablename) {
		$located = parent::locate($this->fn($tablename));
		
		/*
		 * Typo locators do have an educational purpose. While the system should 
		 * not unnecessarily peeve the user with "user" vs "users" naming issues,
		 * it should - if the user is debugging the application - point out that
		 * the naming is incorrect.
		 * 
		 * I'm not entirely happy wit the fact that the system will error inside
		 * spitfire instead of telling the user what file cause the issue.
		 */
		if ($located) {
			trigger_error(
				sprintf('Table %s was misspelled. Prefer %s instead', $tablename, $this->fn($tablename)), 
				E_USER_NOTICE);
		}
		
		return $located;
	}
}