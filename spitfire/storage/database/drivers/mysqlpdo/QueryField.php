<?php namespace spitfire\storage\database\drivers\mysqlpdo;

use spitfire\storage\database\QueryField as ParentClass;

class QueryField extends ParentClass
{
	public function __toString() {
		return "{$this->getQueryTable()}.`{$this->getField()->getName()}`";
	}
}