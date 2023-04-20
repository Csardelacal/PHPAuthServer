<?php namespace spitfire\storage\database\restrictionmaker;

use spitfire\exceptions\PrivateException;
use spitfire\model\Field as Logical;
use spitfire\storage\database\Field;
use spitfire\storage\database\RestrictionGroup;

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
 * The restriction maker class allows Spitfire to generate restrictions according
 * to several provided parameters.
 */
class RestrictionMaker
{
	
	private $workers = [];
	
	/**
	 * Instances the restriction maker, the maker will automatically initialize 
	 * the appropriate workers.
	 */
	public function __construct() {
		$this->workers[] = new SimpleRestrictionWorker();
		$this->workers[] = new ValueNulledWorker();
		$this->workers[] = new CompositeWorker();
		$this->workers[] = new FieldNulledWorker();
		$this->workers[] = new CompositeRemoteFieldWorker();
	}
	
	/**
	 * Loops over the workers defined to make a restriction for the scenario we 
	 * provide.
	 * 
	 * @param string|Logical|Field $field
	 * @param type $operator
	 * @param type $value
	 */
	public function make(RestrictionGroup$parent, $field, $operator, $value) {
		
		/*
		 * Loop over the workers to create a restriction. In case of error, the
		 * workers will return a boolean false or null.
		 */
		foreach ($this->workers as $worker) {
			$r = $worker->make($parent, $field, $operator, $value);
			if ($r) { return $r; }
		}
		
		/*
		 * If nothing could be found, we report the issue back to the user. There's
		 * literally nothing we can do any more.
		 */
		$str = is_object($field)? get_class($field) : $field;
		throw new PrivateException('Restriction for the field ' . $str . ' could not be assembled', 1711101051);
	}
	
}