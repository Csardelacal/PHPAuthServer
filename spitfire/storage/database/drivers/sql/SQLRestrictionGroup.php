<?php namespace spitfire\storage\database\drivers\sql;

use spitfire\storage\database\CompositeRestriction;
use spitfire\storage\database\RestrictionGroup;


abstract class SQLRestrictionGroup extends RestrictionGroup
{
	
	
	public function physicalize() {
		$_ret = [];
		
		foreach ($this as $restriction) {
			if ($restriction instanceof SQLRestrictionGroup) { 
				$_ret = array_merge($_ret, $restriction->physicalize()); 
			}
			
			elseif ($restriction instanceof CompositeRestriction) {
				$_ret = array_merge($_ret, $restriction->makeConnector());
			}
		}
		
		return $_ret;
	}
}
