<?php namespace spitfire\storage\database\restrictionmaker;

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

class CompositeWorker implements WorkerInterface
{
	
	/**
	 * 
	 * @param RestrictionGroup  $parent
	 * @param string $field
	 * @param string $operator
	 * @param mixed  $value
	 */
	public function make(RestrictionGroup$parent, $field, $operator, $value) {
		
		/*
		 * Find the appropriate field for the maker to assemble a restriction. If 
		 * this returns an empty value, then this maker can't assemble a restriction
		 */
		$logical = $parent->getQuery()->getTable()->getSchema()->getField($field);
		$of      = $parent->getQuery()->getTable()->getDb()->getObjectFactory();

		/*
		 * If the field is null or the value is null, then this maker is not a match
		 * for the behavior needed.
		 */
		if ($logical === null || $value === null) { 
			return false; 
		}

		return $of->restrictionCompositeInstance($parent, $logical, $value, $operator);
	}

}
