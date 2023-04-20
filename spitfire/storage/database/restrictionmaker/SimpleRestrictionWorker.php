<?php namespace spitfire\storage\database\restrictionmaker;

use Exception;
use spitfire\storage\database\Query;
use spitfire\storage\database\QueryField;
use spitfire\storage\database\Restriction;
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

class SimpleRestrictionWorker implements WorkerInterface
{
	
	
	/**
	 * 
	 * @param Query $parent
	 * @param QueryField|string $fieldname
	 * @param string $operator
	 * @param mixed $value
	 * 
	 * @return boolean|Restriction
	 */
	public function make(RestrictionGroup$parent, $fieldname, $operator, $value) {
		/*
		 * We need an object factory to create the appropriate elements for the 
		 * restriction.
		 */
		$of = $parent->getQuery()->getTable()->getDb()->getObjectFactory();
		
		/*
		 * If the name of the field passed is a physical field we just use it to 
		 * get a queryField.
		 */
		try {
			$field = $fieldname instanceof QueryField? $fieldname : $parent->getQuery()->getTable()->getLayout()->getField($fieldname);
			return $of->restrictionInstance($parent, $of->queryFieldInstance($parent->getQuery()->getQueryTable(), $field), $value, $operator);	
		} 
		
		/*
		 * If the table threw an exception because there was no such field, we 
		 * catch the exception and return false because we can't figure out how to 
		 * construct the query.
		 */
		catch (Exception $ex) {
			return false;
		}
	}

}