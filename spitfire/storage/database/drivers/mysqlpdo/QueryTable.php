<?php namespace spitfire\storage\database\drivers\mysqlpdo;

use spitfire\storage\database\QueryTable as ParentClass;

class QueryTable extends ParentClass
{
	/**
	 * 
	 * @todo Move the aliasing thing over to the queryTable completely.
	 * @return string
	 */
	public function __toString() {
		return "`{$this->getAlias()}`";
	}

	public function definition() {
		if ($this->isAliased()) {
			return "{$this->getTable()->getLayout()} AS `{$this->getAlias()}`";
		}
		else {
			return "{$this->getTable()->getLayout()}";
		}
	}
}
