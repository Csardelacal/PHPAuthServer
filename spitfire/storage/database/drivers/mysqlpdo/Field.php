<?php namespace spitfire\storage\database\drivers\mysqlpdo;

use spitfire\model\Field as LogicalField;
use spitfire\storage\database\Field as ParentClass;
use \Reference;

class Field extends ParentClass
{
	
	public function columnType() {
		$logical = $this->getLogicalField();
		
		if ($logical instanceof Reference) { 
			$referenced = $this->getReferencedField();
			while($referenced->getReferencedField()) { $referenced = $referenced->getReferencedField(); }
			
			$logical = $referenced->getLogicalField(); 
		}
		
		switch ($logical->getDataType()) {
			case LogicalField::TYPE_INTEGER:
				return 'INT(11)';
			case LogicalField::TYPE_FLOAT:
				return 'DOUBLE';
			case LogicalField::TYPE_LONG:
				return 'BIGINT';
			case LogicalField::TYPE_STRING:
				return "VARCHAR({$logical->getLength()})";
			case LogicalField::TYPE_FILE:
				return "VARCHAR(255)";
			case LogicalField::TYPE_TEXT:
				return "TEXT";
			case LogicalField::TYPE_DATETIME:
				return "DATETIME";
			case LogicalField::TYPE_BOOLEAN:
				return "TINYINT(4)";
		}
	}
	
	public function columnDefinition() {
		$definition = $this->columnType();
		
		if (!$this->getLogicalField()->getNullable())    $definition.= " NOT NULL ";
		if ($this->getLogicalField()->isAutoIncrement()) $definition.= "AUTO_INCREMENT ";
		
		return $definition;
	}

	public function add() {
		$stt = "ALTER TABLE `{$this->getTable()->getLayout()->getTableName()}` 
			ADD COLUMN (`{$this->getName()}` {$this->columnDefinition()} )";
		$this->getTable()->getDb()->execute($stt);
		
		if ($this->getLogicalField()->isPrimary()) {
			$pk = implode(', ', array_keys($this->getTable()->getPrimaryKey()));
			$stt = "ALTER TABLE {$this->getTable()->getLayout()->getTableName()} 
				DROP PRIMARY KEY, 
				ADD PRIMARY KEY(" . $pk . ")";
			$this->getTable()->getDb()->execute($stt);
		}
	}

	public function __toString() {
		return "`{$this->getTable()->getLayout()->getTableName()}`.`{$this->getName()}`";
	}
	
}