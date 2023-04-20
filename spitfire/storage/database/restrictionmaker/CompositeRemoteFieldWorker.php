<?php namespace spitfire\storage\database\restrictionmaker;

use ChildrenField;
use Reference;
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
 * This class creates restrictions in the event of the field provided being provided
 * as a field from a different table that references a field on the query's local
 * table.
 * 
 * This is the exact same as querying by a child field, only that, in this case,
 * the child was never defined on the host table.
 */
class CompositeRemoteFieldWorker implements WorkerInterface
{
	
	/**
	 * 
	 * @param RestrictionGroup $parent
	 * @param Reference $field
	 * @param string    $operator
	 * @param mixed     $value
	 */
	public function make(RestrictionGroup $parent, $field, $operator, $value) {
		$query = $parent->getQuery();
		$of    = $query->getTable()->getDb()->getObjectFactory();
		
		/*
		 * There's several very specific requisites for this component to work. 
		 * The field...
		 * 
		 * * must be a reference
		 * * must not belong to the same table as the query
		 * * must point to the same table as the query
		 */
		if (!($field instanceof Reference && $field->getTable() !== $query->getTable() && $field->getTarget() === $query->getTable()->getSchema())) {
			return false;
		}
		
		/*
		 * Create a child field that connects the tables appropriately. Generally 
		 * speaking, we could be using the field directly. But it makes the whole
		 * ordeal simpler.
		 */
		$child = new ChildrenField($field->getSchema(), $field->getName());
		$child->setSchema($query->getTable()->getSchema());
		
		return $of->restrictionCompositeInstance($parent, $child, $value, $operator);
	}

}
