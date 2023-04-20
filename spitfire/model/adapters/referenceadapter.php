<?php namespace spitfire\model\adapters;

use spitfire\collection\Collection;
use spitfire\exceptions\PrivateException;
use spitfire\Model;
use spitfire\storage\database\Field;
use spitfire\storage\database\Query;

class ReferenceAdapter extends BaseAdapter
{
	
	/**
	 *
	 * @var Query|Model
	 */
	private $remote;
	
	private $raw;
	
	/**
	 *
	 * @var Query|Model
	 */
	private $query;
	
	public function dbSetData($data) {
		$table = $this->getField()->getTarget()->getTable();
		$query = $table->getDb()->table($table->getModel()->getName())->getAll();
		$physical = $this->getField()->getPhysical();
		
		foreach ($physical as $p) {
			/* @var $p Field */
			$query->addRestriction($p->getReferencedField()->getName(), $data[$p->getName()]);
		}
		
		$this->query  = $query;
		$this->raw = $data;
		
		/*
		 * We clone this in order to prevent the query from generating race 
		 * conditions when having multiple objects. This is one of those instances
		 * where I hate myself for having included the result set object in the 
		 * query.
		 * 
		 * TODO: This should stop being an issue once the resultset object has been
		 * moved out of the query.
		 */
		$this->remote = $this->query === null? null : clone $this->query;
	}
	
	public function dbGetData() {
		$field = $this->getField();
		$physical = $field->getPhysical();
		$_return = Array();
		
		if ($this->query instanceof Model) {
			#Get the raw data from the donor model
			$modeldata = $this->query->getPrimaryData();
			foreach ($physical as $p) {
				$_return[$p->getName()] = $modeldata[$p->getReferencedField()->getName()];
			}
		} 
		elseif ($this->query instanceof Query) {
			return $this->raw;
		} 
		elseif ($this->query === null) {
			foreach ($physical as $p) {
				$_return[$p->getName()] = null;
			}
		} 
		else {
			throw new PrivateException('Adapter holds invalid data');
		}
		
		return $_return;
	}
	
	public function usrGetData() {
		if ($this->query instanceof Query) {
			return $this->query = $this->query->fetch();
		} else {
			return $this->query;
		}
	}
	
	public function usrSetData($data) {
		//Check if the incoming data is an int
		if ( !$data instanceof Model && !is_null($data)) {
			throw new PrivateException('This adapter only accepts models');
		}
		//Make sure the finally stored data is an integer.
		$this->query = $data;
	}
	
	public function isSynced() {
		if ($this->query instanceof Query) {
			return true; //The data has not been changed
		}
		
		$this->remote = $ma = $this->remote instanceof Query? $this->remote->fetch() : $this->remote;
		$this->query  = $mb = $this->query  instanceof Query? $this->query->fetch()  : $this->query;
		
		$pka = $ma? $ma->getPrimaryData() : null;
		$pkb = $mb? $mb->getPrimaryData() : null;
		
		return $pka == $pkb;
	}
	
	
	/**
	 * Sets the data as stored to the database and therefore as synced. After 
	 * committing, rolling back will return the current value.
	 */
	public function commit() {
		#Now we can safely say that the data stored on the remote and local sets 
		#is equal. Therefore we can replace the old remote value.
		$this->remote = $this->query;
	}
	
	/**
	 * Resets the data to the status the database holds. This is especially 
	 * interesting if you want to undo certain changes.
	 */
	public function rollback() {
		$this->query = $this->remote;
	}
	
	public function getDependencies() {
		return new Collection($this->query instanceof Query || $this->query === null? null : $this->query);
	}
}

