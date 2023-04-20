<?php

use spitfire\storage\database\Field as PhysicalField;
use spitfire\storage\database\Schema;
use spitfire\model\Field;
use spitfire\Model;
use spitfire\model\adapters\ManyToManyAdapter;

class ManyToManyField extends ChildrenField
{

	/** @var Schema */
	private $meta;
	
	public function __construct($model) {
		parent::__construct($model, null);
	}
	
	public function getRole() {
		return parent::getModel()->getName();
	}

	/** @return Schema */
	public function getTarget() {
		if ($this->meta) { return $this->target; }
		
		$src    = $this->getModel()->getName();
		$target = $this->target;
		
		$first  = ($src > $target)? $target : $src;
		$second = ($first == $src)? $target : $src;
		
		if ($src === $target) { $targetalias = $target . '_1'; }
		else                  { $targetalias = $target; }
		
		if (!$this->getTable()->getDb()->getTableCache()->contains("{$first}_{$second}")) {
			
			$model = $this->meta = new Schema("{$first}_{$second}");
			unset($model->_id);

			$model->{$src}         = new Reference($src);
			$model->{$targetalias} = new Reference($target);

			$model->{$src}->setPrimary(true);
			$model->{$targetalias}->setPrimary(true);
			
			$model->index($model->{$src}, $model->{$targetalias})->setPrimary(true);

			#Register the table
			$this->getModel()->getTable()->getDb()->table($model);
		} else {
			$this->meta = $this->getTable()->getDb()->table("{$first}_{$second}")->getSchema();
		}
		
		#Return the remote model
		$this->target = $this->getModel()->getTable()->getDb()->table($this->target)->getSchema();
		return $this->target;
	}

	/**
	 * @param Schema $schema
	 * @return PhysicalField
	 */
	public function getModelField($schema) {
		return $this->meta->getField($schema->getName());
	}
	
	/**
	 * Returns the table that connects the two tables to form a many to many 
	 * relationship
	 * 
	 * @return Schema
	 */
	public function getBridge() {
		if ($this->meta) { return $this->meta; }
		
		$this->getTarget();
		return $this->meta;
	}

	public function getDataType() {
		return Field::TYPE_BRIDGED;
	}
	
	public function getAdapter(Model $model) {
		return new ManyToManyAdapter($this, $model);
	}
	
	public function getConnectorQueries(spitfire\storage\database\Query$parent) {
		$table = $this->getTarget()->getTable();
		$of    = $table->getDb()->getObjectFactory();
		$query = $table->getDb()->getObjectFactory()->queryInstance($table);
		$query->setAliased(true);
		
		if ($this->target !== $this->getModel()) {
			#In case the models are different we just return the connectors via a simple route.
			$bridge = $this->getBridge()->getTable();
			$route  = $bridge->getDb()->getObjectFactory()->queryInstance($bridge);
			$fields = $this->getBridge()->getFields();
			$route->setAliased(true);
			
			foreach ($fields as $field) {
				if ($field->getTarget() === $this->getModel()) {
					$physical = $field->getPhysical();
					foreach ($physical as $p) { $route->where($of->queryFieldInstance($route->getQueryTable(), $p), $of->queryFieldInstance($parent->getQueryTable(), $p->getReferencedField()));}
				} else {
					$physical = $field->getPhysical();
					foreach ($physical as $p) { $query->where($of->queryFieldInstance($route->getQueryTable(), $p), $of->queryFieldInstance($query->getQueryTable(), $p->getReferencedField()));}
				}
			}
			return Array($route, $query);
			
		} else {
			#In case the models are the same, well... That's hell
			$route1 = $this->getBridge()->getTable()->getAll();
			$route2 = $this->getBridge()->getTable()->getAll();
			$fields = $this->getBridge()->getFields();
			
			#Alias the routes so they don't collide
			$route1->setAliased(true);
			$route2->setAliased(true);
			
			$f1  = reset($fields);
			$f2  = end($fields);
			$f1p = $f1->getPhysical();
			$f2p = $f2->getPhysical();
			
			#Start with routes from src
			foreach ($f1p as $p) {$route1->where($of->queryFieldInstance($route1->getQueryTable(), $p), $of->queryFieldInstance($parent->getQueryTable(), $p->getReferencedField()));}
			foreach ($f2p as $p) {$route2->where($of->queryFieldInstance($route2->getQueryTable(), $p), $of->queryFieldInstance($parent->getQueryTable(), $p->getReferencedField()));}
			
			#Exclude repeated results from Route2
			$group = $route2->group(\spitfire\storage\database\RestrictionGroup::TYPE_OR);
			foreach ($f1p as $k => $v) {$group->where($of->queryFieldInstance($route2->getQueryTable(), $v), '<>', $of->queryFieldInstance($route2->getQueryTable(), $f2p[$k]));}
			
			#Link back
			$groupback = $query->group(spitfire\storage\database\RestrictionGroup::TYPE_OR);
			$r1group   = $groupback->group(spitfire\storage\database\RestrictionGroup::TYPE_AND);
			$r2group   = $groupback->group(spitfire\storage\database\RestrictionGroup::TYPE_AND);
			
			#Note that the fields are now swaped
			foreach ($f2p as $p) {$r1group->addRestriction($of->queryFieldInstance($route1->getQueryTable(), $p), $of->queryFieldInstance($query->getQueryTable(), $p->getReferencedField()));}
			foreach ($f1p as $p) {$r2group->addRestriction($of->queryFieldInstance($route2->getQueryTable(), $p), $of->queryFieldInstance($query->getQueryTable(), $p->getReferencedField()));}
			
			return Array($route1, $route2, $query);
		}
	}
	
}
