<?php namespace spitfire\storage\database\drivers\mysqlpdo;

use spitfire\storage\database\Restriction as AbstractRestriction;

class Restriction extends AbstractRestriction
{
	public function __toString() {
		$value = $this->getValue();

		if (is_array($value)) {
			foreach ($value as &$v) {
				$v = $this->getTable()->getDb()->quote($v);
			}

			$quoted = implode(',', $value);
			return "{$this->getField()} {$this->getOperator()} ({$quoted})";
		}

		elseif ($value instanceof QueryField) {
			return "{$this->getField()} {$this->getOperator()} {$this->getValue()}";
		}
		elseif ($value === null) {
			$operator = in_array($this->getOperator(), ['IS', '='])? 'IS' : 'IS NOT';
			return "{$this->getField()} {$operator} NULL";
		}
		else {
			$quoted = $this->getTable()->getDb()->quote($value);
			return "{$this->getField()} {$this->getOperator()} {$quoted}";
		}
	}
	
}
