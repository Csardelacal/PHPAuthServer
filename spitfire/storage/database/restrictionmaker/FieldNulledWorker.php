<?php namespace spitfire\storage\database\restrictionmaker;

use ChildrenField;
use Reference;
use spitfire\storage\database\Query;
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

/**
 * 
 * @todo This should handle the specific case of null restrictions.
 */
class FieldNulledWorker implements WorkerInterface
{
	
	/**
	 * When field is provided as a null value, the system will automatically 
	 * generate the appropriate restrictions to search any field in the table.
	 * 
	 * Please consider that this may leak data if provided to the wrong person.
	 * 
	 * E.g. If the user "guesses" information about a record, the system will
	 * return the record. For example, a wildcard search to the user table may
	 * provide a user's account when providing some private information the
	 * application may hold, like a Social security number.
	 * 
	 * @param Query $parent
	 * @param null   $field
	 * @param string $operator
	 * @param mixed  $value
	 * 
	 * @return boolean|Restriction[]
	 */
	public function make(RestrictionGroup$parent, $field, $operator, $value) {
		
		/*
		 * Check if the field is nulled, this means that the user wants the system
		 * to go over every field.
		 */
		if ($field !== null) { return false; }
		
		/*
		 * Acquire the object factory for the driver, which allows us to retrieve
		 * driver tailored components for the database.
		 */
		$of     = $parent->getQuery()->getTable()->getDb()->getObjectFactory();
		$fields = $parent->getQuery()->getTable()->getSchema()->getFields();
		$restr  = $of->restrictionGroupInstance($parent, RestrictionGroup::TYPE_AND);
		
		/*
		 * We use only the simple fields to broad search for terms across the db.
		 * This avoids the search returning data that is used to connect tables 
		 * together.
		 */
		$simple = array_filter($fields, function ($e) {
			return !$e instanceof Reference && !$e instanceof ChildrenField;
		});
		
		
		foreach ($simple as $f) {
			$physical = $f->getPhysical();
			$restr->add($of->restrictionInstance($parent, reset($physical), $value, $operator));
		}
		
		return $restr;
	}

}