<?php

use spitfire\model\Field;
use spitfire\Model;
use spitfire\model\adapters\ChildrenAdapter;
use spitfire\exceptions\PrivateException;
use spitfire\storage\database\Schema;

/**
 * The children field allows the application to maintain a relationship in the 
 * database that acts like an array of models that depend on the current model.
 * 
 * @todo Move to a proper namespaced class, but we need to find a mechanism for
 * aliasing classes that works properly.
 */
class ChildrenField extends Field
{
	/** @var string|null */
	protected $role;
	/** @var string|Schema|Model */
	protected $target;

	/**
	 * @param string|Schema|Model $target
	 * @param string              $role
	 */
	public function __construct($target, $role) {
		$this->target = $target;
		$this->role   = $role;
	}
	
	/**
	 * Returns the model this fields is pointing to, the child model. It is referred
	 * as target due to the fact that this field is pointing at it.
	 * 
	 * @return Schema
	 */
	public function getTarget() {
		
		#If the target is actually a class name.
		if (is_string($this->target) && Strings::endsWith($this->target, 'Model')) {
			#$this->target = trim(substr($this->target, 0, 0 - strlen('Model')), '\/');
		}
		
		#Check if the passed argument already is a model
		if ($this->target instanceof Schema) {
			return $this->target;
		} 
		elseif ($this->target === $this->getModel()->getName()) {
			return $this->target = $this->getModel();
		}
		else {
			return $this->target = $this->getModel()->getTable()->getDB()->table($this->target)->getSchema();
		}
	}
	
	/**
	 * Retrieves the field that this child field references. Although the children
	 * field has no representation in the DBMS it does provide a simplified 
	 * mechanism to access the data in a table that references the current model.
	 * 
	 * If the programmer provided a target we can just check that the field exists
	 * and return it, otherwise we need to search for a field that references
	 * this model and return that.
	 * 
	 * @return \Reference
	 * @throws PrivateException
	 */
	public function getReferencedField() {
		if (!empty($this->role)) {
			#If a role is predefined, we already know what to get
			return $this->getTarget()->getField($this->role);
		} else {
			$fields = $this->getTarget()->getFields();
			
			#Since we could have several items pointing at our Schema we will be
			#filtering the remote fields looking for candidates.
			$candidates = array_filter($fields, function ($f) {
				return $f instanceof \Reference && $f->getTarget() === $this->getModel();
			});
			
			#If there were no candidates we need to let the programmer know
			if (empty($candidates)) {
				throw new PrivateException('Children field pointing at a model that does not reference it back.');
			}
			
			#Once we have the candidates we return the first we found, since no other
			#option was provided.
			return reset($candidates);
		}
	}
	
	public function getRole() {
		return $this->role;
	}
	
	public function getPhysical() {
		return Array();
	}
	
	/*
	 * Returns the data type, this method allows the logical field to determine
	 * what kind of physical fields we're gonna need to store the data for this
	 * type.
	 */
	public function getDataType() {
		return Field::TYPE_CHILDREN;
	}
	
	/**
	 * This method used to provide a mechanism to finding the target. Since 
	 * getReferencedField does the job and does it better, this method is deprecated.
	 * 
	 * @deprecated since version 0.1-dev 20160905
	 * @return \Reference
	 */
	public function findReference() {
		$model = $this->getTarget();
		
		if ($model->getField($this->getRole())) return $model->getField($this->getRole());
		else {
			$fields = $model->getFields();
			foreach ($fields as $field) {
				if ($field instanceof Reference && $field->getTarget() === $this->getModel()) 
					return $field;
			}
		}
	}

	public function getAdapter(Model $model) {
		return new ChildrenAdapter($this, $model);
	}

	public function getConnectorQueries(\spitfire\storage\database\Query $parent) {
		$query = $this->getTarget()->getTable()->getCollection()->getAll();
		$of    = $this->getTarget()->getTable()->getDb()->getObjectFactory();
		$query->setAliased(true);
		
		foreach ($this->getReferencedField()->getPhysical() as $p) {
			$query->addRestriction($of->queryFieldInstance($parent->getQueryTable(), $p->getReferencedField()), $of->queryFieldInstance($query->getQueryTable(), $p));
		}
		
		return Array($query);
	}

}
