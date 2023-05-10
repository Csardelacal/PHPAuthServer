<?php namespace spitfire;

use spitfire\exceptions\PrivateException;
use spitfire\storage\database\Schema;
use spitfire\storage\database\Table;

/**
 * This class allows to track changes on database data along the use of a program
 * and creates interactions with the database in a safe way.
 *
 * @todo Make this class implement Iterator
 * @author César de la Cal <cesar@magic3w.com>
 */
abstract class Model
{
	
	/**
	 * The actual data that the record contains. The record is basically a wrapper
	 * around the array that allows to validate data on the go and to alert the
	 * programmer about inconsistent types.
	 *
	 * @var \spitfire\model\adapters\AdapterInterface[]
	 */
	private $data;
	
	/**
	 * Keeps information about the table that owns the record this Model represents.
	 * This allows it to power functions like store that require knowledge about
	 * the database keeping the information.
	 *
	 * @var Table
	 */
	private $table;
	
	#Status vars
	private $new = false;
	
	/**
	 * Creates a new record.
	 *
	 * @param Table $table DB Table this record belongs to. Easiest way
	 *                       to get this is by using $this->model->*tablename*
	 *
	 * @param mixed $data  Attention! This parameter is intended to be
	 *                       used by the system. To create a new record, leave
	 *                       empty and use setData.
	 */
	public function __construct(Table$table = null, $data = null)
	{
		
		$this->table   = $table;
		$this->new     = empty($data);
		
		$this->makeAdapters();
		$this->populateAdapters($data);
	}
	
	/**
	 * This method is used to generate the 'template' for the table that allows
	 * spitfire to automatically generate tables and allows it to check the types
	 * of data and fix tables.
	 *
	 * @param Schema $schema
	 * @return Schema
	 * @abstract
	 */
	abstract public function definitions(Schema$schema);
	
	/**
	 * Returns the data this record currently contains as associative array.
	 * Remember that this data COULD be invalid when using setData to provide
	 * it.
	 *
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}
	
	/**
	 * This method stores the data of this record to the database. In case
	 * of database error it throws an Exception and leaves the state of the
	 * record unchanged.
	 *
	 * @throws PrivateException
	 */
	public function store()
	{
		$dependencies = $this->getDependencies();
		
		$processed = collect([$this]);
		
		while ($d = $dependencies->shift()) {
			if (!$processed->contains($d)) {
				$processed->push($d);
				$dependencies->add($d->getDependencies());
			}
		}
		
		#Check if onbeforesave is there and use it.
		$processed->each(function ($e) {
			if (method_exists($e, 'onbeforesave')) {
				$e->onbeforesave();
			}
		});
		
		$processed->each(function ($e) {
			$e->write();
		});
	}
	
	public function write()
	{
		#Decide whether to insert or update depending on the Model
		if ($this->new) {
			#Get the autoincrement field
			$id = $this->table->getCollection()->insert($this);
			$ai = $this->table->getAutoIncrement();
			$ad = $ai? $this->data[$ai->getName()]->dbGetData() : null;
			
			#If the autoincrement field is empty set the new DB given id
			if ($ai && !reset($ad)) {
				$this->data[$ai->getName()]->dbSetData(array($ai->getName() => $id));
			}
		}
		else {
			$this->table->getCollection()->update($this);
		}
		
		$this->new = false;
		
		foreach ($this->data as $value) {
			$value->commit();
		}
	}
	
	/**
	 * Returns the values of the fields included in this records primary
	 * fields
	 *
	 * @todo Find better function name
	 * @return array
	 */
	public function getPrimaryData()
	{
		$primaryFields = $this->table->getPrimaryKey()->getFields();
		$ret = array();
		
		foreach ($primaryFields as $field) {
			$logical = $field->getLogicalField();
			$ret = array_merge($ret, $this->data[$logical->getName()]->dbGetData());
		}
		
		return $ret;
	}
	
	public function getQuery()
	{
		$query     = $this->getTable()->getDb()->getObjectFactory()->queryInstance($this->getTable());
		$primaries = $this->table->getModel()->getPrimary()->getFields();
		
		foreach ($primaries as $primary) {
			$name = $primary->getName();
			$query->addRestriction($name, $this->$name);
		}
		
		return $query;
	}
	
	/**
	 * Returns the table this record belongs to.
	 *
	 * @return \spitfire\storage\database\Table
	 */
	public function getTable()
	{
		return $this->table;
	}
	
	public function __set($field, $value)
	{
		
		if (!isset($this->data[$field])) {
			throw new PrivateException("Setting non existent field: " . $field);
		}
		
		$this->data[$field]->usrSetData($value);
	}
	
	public function __get($field)
	{
		#If the field is in the record we return it's contents
		if (isset($this->data[$field])) {
			return $this->data[$field]->usrGetData();
		} else {
			//TODO: In case debug is enabled this should throw an exception
			return null;
		}
	}
	
	public function __isset($name)
	{
		return (array_key_exists($name, $this->data));
	}
	
	//TODO: This now breaks due to the adapters
	public function serialize()
	{
		$data = array();
		foreach ($this->data as $adapter) {
			if (! $adapter->isSynced()) {
				throw new PrivateException("Database record cannot be serialized out of sync");
			}
			$data = array_merge($data, $adapter->dbGetData());
		}
		
		$output = array();
		$output['model'] = $this->table->getModel()->getName();
		$output['data']  = $data;
		
		return serialize($output);
	}
	
	public function unserialize($serialized)
	{
		
		$input = unserialize($serialized);
		$this->table = db()->table($input['model']);
		
		$this->makeAdapters();
		$this->populateAdapters($input['data']);
	}
	
	public function onbeforesave()
	{
	}
	
	public function __toString()
	{
		return sprintf('%s(%s)', $this->getTable()->getModel()->getName(), implode(',', $this->getPrimaryData()));
	}
	
	public function delete()
	{
		$this->table->getCollection()->delete($this);
	}
	
	/**
	 * Increments a value on high read/write environments. Using update can
	 * cause data to be corrupted. Increment requires the data to be in sync
	 * aka. stored to database.
	 *
	 * @param String $key
	 * @param int|float $diff
	 * @throws PrivateException
	 */
	public function increment($key, $diff = 1)
	{
		$this->table->increment($this, $key, $diff);
	}
	
	protected function makeAdapters()
	{
		#If there is no table defined there is no need to create adapters
		if ($this->table === null) {
			return;
		}
		
		$fields = $this->getTable()->getModel()->getFields();
		foreach ($fields as $field) {
			$this->data[$field->getName()] = $field->getAdapter($this);
		}
	}
	
	protected function populateAdapters($data)
	{
		#If the set carries no data, why bother reading?
		if (empty($data)) {
			return;
		}
		
		#Retrieves the full list of fields this adapter needs to populate
		$fields = $this->getTable()->getModel()->getFields();
		
		#Loops through the fields retrieving the physical fields
		foreach ($fields as $field) {
			$physical = $field->getPhysical();
			$current  = array();
			
			#The physical fields are matched to the content and it is assigned.
			foreach ($physical as $p) {
				$current[$p->getName()] = $data[$p->getName()];
			}
			
			#Set the data into the adapter and let it work it's magic.
			$this->data[$field->getName()]->dbSetData($current);
		}
	}
	
	public function getDependencies()
	{
		
		$dependencies = collect($this->data)
			->each(function ($e) {
				return $e->getDependencies();
			})
			->filter()
			->flatten();
		
		return $dependencies;
	}
}
