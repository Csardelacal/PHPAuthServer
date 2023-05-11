<?php namespace commishes\qa\runner\coverage;

class Utils
{
	
	public static function merge(array $a, array $b) : array
	{	
			
		/**
		 * First, check if the keys of a are missing in b.
		 * If the do not exist, we can proceed to just use the raw data from
		 * a to populate b. Merge that with the same operation in reverse. That
		 * should leave us with a result that contains all the entries that are
		 * not in both a and b.
		 */
		$_result = array_merge(
			self::addMissing($a, $b),
			self::addMissing($b, $a)
		);
		
		/**
		 * The only keys left to process are those that appear in both arrays. Those need
		 * to be manually processed.
		 */
		foreach (array_keys(array_intersect_key($a, $b)) as $key) {
			
			if (!isset($b[$key])) {
				continue;
			}
			
			assert(is_array($a[$key]) || is_int($a[$key]));
			
			/**
			 * First, check if the keys of a are missing in b.
			 * If the do not exist, we can proceed to just use the raw data from
			 * a to populate b.
			 */
			if (is_array($a[$key])) {
				$_result[$key] = self::merge($a[$key], $b[$key]);
			}
			
			else {
				$_result[$key] = max($a[$key], $b[$key]);
			}
		}
		
		return $_result;
	}
	
	public static function addMissing(array &$a, array &$b) : array
	{
		$_result = [];
		
		foreach ($a as $key => $value) {
			if (!array_key_exists($key, $b)) {
				$_result[$key] = $value;
			}
		}
		
		return $_result;
	}
}
