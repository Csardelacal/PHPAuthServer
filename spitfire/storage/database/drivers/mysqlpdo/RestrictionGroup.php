<?php namespace spitfire\storage\database\drivers\mysqlpdo;

use spitfire\storage\database\drivers\sql\SQLRestrictionGroup;

class RestrictionGroup extends SQLRestrictionGroup
{
	public function __toString() {
		if ($this->isEmpty()) { return ''; }
		return sprintf('(%s)', implode(' ' . $this->getType() .' ', $this->getRestrictions()));
	}
}