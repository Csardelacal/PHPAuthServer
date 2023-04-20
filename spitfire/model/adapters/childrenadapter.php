<?php namespace spitfire\model\adapters;

use ChildrenField;
use spitfire\Model;
use Iterator;
use ArrayAccess;
use spitfire\collection\Collection;

class ChildrenAdapter implements ArrayAccess, Iterator, AdapterInterface
{
	/**
	 * The field the parent uses to refer to this element.
	 *
	 * @var \spitfire\model\ManyToManyField
	 */
	private $field;
	
	private $original;
	private $parent;
	private $children;
	
	public function __construct(ChildrenField$field, Model$model, $data = null) {
		$this->field  = $field;
		$this->parent = $model;
		$this->children = $data;
		$this->original = $data;
	}
	
	/**
	 * Returns the query that would be used to retrieve the elements for this 
	 * adapter. This can be used to add restrictions and query the related records
	 * 
	 * @return \spitfire\storage\database\Query
	 */
	public function getQuery() {
		
		$query = $this->field->getTable()->getDb()->getObjectFactory()
				  ->queryInstance($this->field->getTarget()->getTable());
				
		return $query->where($this->field->getReferencedField()->getName(), $this->parent->getQuery());
		
	}
	
	public function pluck() {
		if ($this->children !== null) { return reset($this->children); }
		
		return $this->getQuery()->fetch();
	}
	
	public function toArray() {
		if ($this->children !== null) { return $this->children; }
		
		/*
		 * Inform the children that the parent being worked on is this
		 */
		$this->children = $this->original = $this->getQuery()->fetchAll()->each(function ($c) {
			$c->{$this->field->getReferencedField()->getName()} = $this->parent;
			return $c;
		})->toArray();
		
		return $this->children;
	}

	public function current() : mixed {
		if ($this->children === null) $this->toArray();
		return current($this->children);
	}

	public function key() {
		if ($this->children === null) $this->toArray();
		return key($this->children);
	}

	public function next() : void {
		if ($this->children === null) $this->toArray();
		next($this->children);
	}

	public function rewind() : void {
		if ($this->children === null) $this->toArray();
		reset($this->children);
	}

	public function valid() : bool {
		if ($this->children === null) $this->toArray();
		return !!current($this->children);
	}

	public function offsetExists($offset) : bool {
		if ($this->children === null) $this->toArray();
		return isset($this->children[$offset]);
		
	}

	public function offsetGet($offset) : mixed {
		if ($this->children === null) $this->toArray();
		return $this->children[$offset];
	}

	public function offsetSet($offset, $value) : void {
		if ($this->children === null) { $this->toArray(); }
		
		$previous = isset($this->children[$offset])? $this->children[$offset] : null;
		
		if ($offset === null) { $this->children[] = $value; }
		else                  { $this->children[$offset] = $value; }
		
		#Commit the changes to the database.
		$role  = $this->getField()->getRole();
		
		#We set the value but do not yet commit it, this will happen whenever the 
		#parent model is written.
		$value->{$role} = $this->getModel();
		
		if ($previous) {
			$previous->{$role} = null;
		}
	}

	public function offsetUnset($offset) : void {
		if ($this->children === null) $this->toArray();
		unset($this->children[$offset]);
	}
	
	/**
	 * 
	 * @todo If the element list has changed, the database should sever the connections
	 *  between the elements and their children.
	 * @return type
	 */
	public function commit() {
		return;
	}

	public function dbGetData() {
		return Array();
	}
	
	/**
	 * This method does nothing as this field has no direct data in the DBMS and 
	 * therefore it just ignores whatever the database tries to input.
	 * 
	 * @param mixed $data
	 */
	public function dbSetData($data) {
		return;
	}
	
	/**
	 * Returns the parent model for this adapter. This allows any application to 
	 * trace what adapter this adapter belongs to.
	 * 
	 * @return \Model
	 */
	public function getModel() {
		return $this->parent;
	}
	
	public function isSynced() {
		return true;
	}

	public function rollback() {
		return true;
	}

	public function usrGetData() {
		return $this;
	}
	
	/**
	 * Defines the data inside this adapter. In case the user is trying to set 
	 * this adapter as the source for itself, which can happen in case the user
	 * is reading the adapter and expecting himself to save it back this function
	 * will do nothing.
	 * 
	 * @param \spitfire\model\adapters\ManyToManyAdapter|Model[] $data
	 * @todo Fix to allow for user input
	 * @throws \spitfire\exceptions\PrivateException
	 */
	public function usrSetData($data) {
		if ($data === $this) {
			return;
		}
		
		foreach ($this->children as $child) {
			$role  = $this->getField()->getRole();

			#We set the value but do not yet commit it, this will happen whenever the 
			#parent model is written.
			$child->{$role} = null;
		}
		
		if ($data instanceof ManyToManyAdapter) {
			$this->children = $data->toArray();
		} elseif (is_array($data)) {
			$this->children = $data;
		} else {
			throw new \spitfire\exceptions\PrivateException('Invalid data. Requires adapter or array');
		}
		
		foreach ($this->children as $child) {
			$role  = $this->getField()->getRole();

			#We set the value but do not yet commit it, this will happen whenever the 
			#parent model is written.
			$child->{$role} = $this->getModel();
		}
	}

	public function getField() {
		return $this->field;
	}
	
	public function __toString() {
		return "Array()";
	}
	
	public function getDependencies() {
		return Collection::fromArray($this->children === null? [] : array_merge($this->children, $this->original));
	}
}
