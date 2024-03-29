<?php namespace spitfire\model\adapters;

use \ManyToManyField;
use spitfire\Model;
use \Iterator;
use \ArrayAccess;
use spitfire\storage\database\RestrictionGroup;

/**
 * This adapter allows the user to access Many to many relations inside the 
 * database as if it was an array with sorted data. This makes it easy to iterate
 * over records or do actions on them. Please note that some of the actions 
 * performed on this records are very database intensive and are therefore to be
 * used with care.
 */
class ManyToManyAdapter implements ArrayAccess, Iterator, AdapterInterface
{
	/**
	 * The field the parent uses to refer to this element. This allows the app to 
	 * identify the kind of data it can receive and what table it is referring.
	 * @var \spitfire\model\ManyToManyField
	 */
	private $field;
	
	/**
	 * The 'parent' record. This helps locating the children field that should be
	 * fetched when accessing the content of the adapter.
	 * @var \Model 
	 */
	private $parent;
	
	/**
	 * The data stored into the adapter. This helps caching the data and keeping 
	 * a diff in case the data is modified.
	 * @var \Model[] 
	 */
	private $children;
	
	/**
	 * Creates a new adapter for fields that hold many to many data. This adapter
	 * allows you to access the related data for a 'parent' field on the related
	 * table.
	 * 
	 * You can use this adapter to develop applications simultating that the data 
	 * base can store arrays on relational stores.
	 * 
	 * @param ManyToManyField $field
	 * @param Model $model
	 * @param void $data - deprecated. Should no longer be used.
	 */
	public function __construct(ManyToManyField$field, Model$model, $data = null) {
		$this->field  = $field;
		$this->parent = $model;
		
		if ($data !== null) { 
			$this->children = $data;
		}
	}
	
	/**
	 * Gets the query that fetches the current set of data located inside the 
	 * database for this adapter. This allows the adapter to 'initialize' itself
	 * in case there was no data inside it when the user requested it.
	 * 
	 * @return \spitfire\storage\database\Query
	 */
	public function getQuery() {
		$table  = $this->field->getTarget()->getTable();
		$fields = $table->getModel()->getFields();
		$found  = null;
		
		#Locates the field that relates to the parent model
		foreach ($fields as $field) {
			if ($field instanceof ManyToManyField && $field->getTarget() === $this->field->getModel()) {
				$found = $field;
			}
		}
		
		return $table->getDB()->getObjectFactory()->queryInstance($table)->addRestriction($found->getName(), $this->parent->getQuery());
		
	}
	
	public function getBridgeRecordsQuery() {
		#Get the fields in the Bridge table
		$bridge_fields = $this->field->getBridge()->getFields();

		#Prepare a query for the records that are connected by this field
		$bridge = $this->field->getBridge()->getTable();
		$query  = $bridge->getDB()->getObjectFactory()->queryInstance($bridge);

		#We create a group to handle many to many connections that connect to the same model
		$group = $query->group();

		#Write the query
		foreach($bridge_fields as $f) {
			$pk = $this->parent->getPrimaryData();
			$sg = $group->group(RestrictionGroup::TYPE_AND);
			if ($f->getTarget() === $this->field->getModel()) {
				foreach($f->getPhysical() as $p) {$sg->addRestriction($p, array_shift($pk));}
			}
		}
		return $query;
	}
	
	/**
	 * @deprecated since version 0.1-dev
	 */
	public function store() {
		$bridge_records = $this->getBridgeRecordsQuery()->fetchAll();

		foreach($bridge_records as $r) $r->delete();

		//@todo: Change for definitive.
		$value = $this->toArray();
		foreach($value as $child) {
			$insert = new BridgeAdapter($this->field, $this->parent, $child);
			$insert->makeRecord()->store();
		}
	}
	
	/**
	 * Returns the field that contains this data. This allows to query the table or 
	 * target with ease.
	 * 
	 * @return \ManyToManyField
	 */
	public function getField() {
		return $this->field;
	}
	
	public function toArray() {
		if ($this->children) return $this->children;
		$this->children = $this->getQuery()->fetchAll()->toArray();
		return $this->children;
	}

	public function current() {
		if (!$this->children) $this->toArray();
		return current($this->children);
	}

	public function key() {
		if (!$this->children) $this->toArray();
		return key($this->children);
	}

	public function next() : void {
		if (!$this->children) $this->toArray();
		next($this->children);
	}

	public function rewind() : void {
		if (!$this->children) $this->toArray();
		reset($this->children);
	}

	public function valid() : bool {
		if (!$this->children) $this->toArray();
		return !!current($this->children);
	}

	public function offsetExists($offset) : bool {
		if (!$this->children) $this->toArray();
		return isset($this->children[$offset]);
		
	}

	public function offsetGet($offset) {
		if (!$this->children) $this->toArray();
		return $this->children[$offset];
	}

	public function offsetSet($offset, $value) : void {
		if (!$this->children) $this->toArray();
		$this->children[$offset] = $value;
	}

	public function offsetUnset($offset) : void {
		if (!$this->children) $this->toArray();
		unset($this->children[$offset]);
	}

	public function commit() {
		
		if ($this->children === null) { return; }
		
		$value = $this->children;
		
		$this->getBridgeRecordsQuery()->all()->each(function ($e) { $e->delete(); });

		//@todo: Change for definitive.
		foreach($value as $child) {
			$insert = new BridgeAdapter($this->field, $this->parent, $child);
			$insert->makeRecord()->write();
		}
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
	 * @return \spitfire\Model
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
	 * @throws \spitfire\exceptions\PrivateException
	 */
	public function usrSetData($data) {
		if ($data === $this) {
			return;
		}
		
		if ($data instanceof ManyToManyAdapter) {
			$this->children = $data->toArray();
		} elseif (is_array($data)) {
			$this->children = $data;
		} else {
			throw new \spitfire\exceptions\PrivateException('Invalid data. Requires adapter or array');
		}
	}
	
	public function getDependencies() {
		//TODO: Needs to allow for versioning like children adapter does
		return collect();
	}

}
