<?php namespace commishes\qa\runner\fixtures;

use PDO;
use SimpleXMLElement;

class SchemaProcessor
{
	
	private PDO $connection;
	private string $name;
	
	/**
	 * 
	 * @todo Add logger interface so database queries can be logged
	 * @param PDO $connection
	 */
	public function __construct(PDO $connection, string $name)
	{
		$this->connection = $connection;
		$this->name = $name;
	}
	
	public function process(SimpleXMLElement $table)
	{
		assert($table->getName() === 'table');
		
		$tablename = (string)$table->attributes()["name"];
		$records = $table->children();
		
		foreach ($records as $record) {
			switch ($record->getName()) {
				case 'truncate':
					$this->truncate($tablename);
					break;
				case 'record':
					$this->insert($tablename, ((array)$record->attributes())['@attributes']);
					break;
				case 'delete':
					$this->delete($tablename, ((array)$record->attributes())['@attributes']);
					break;
			}
		}
	}
	
	private function truncate(string $tablename)
	{
		$this->exec(sprintf('TRUNCATE %s', $this->name . '.' . $tablename));			
	}
	
	private function delete(string $tablename, array $values)
	{
		$this->exec(sprintf(
			'DELETE FROM `%s`.`%s` WHERE %s',
			$this->name,
			$tablename,
			$this->makeRestrictions($values)
		));
	}
	
	private function makeRestrictions(array $values)
	{	
		$_ret = [];
		
		foreach ($values as $key => $value) {
			$_ret[] = sprintf('`%s` = %s', $key, $this->connection->quote($value));
		}
		
		return implode(' AND ', $_ret);
	}
	
	private function insert(string $tablename, array $values) : void
	{
		$_values = array_map(
			fn($v) => $this->connection->quote($v),
			$values
		);
		
		$keys = array_map(
			fn(string $e) : string => sprintf('`%s`', $e),
			array_keys($values)
		);
		
		$this->exec(sprintf(
			'INSERT INTO `%s`.`%s` (%s) VALUES (%s)',
			$this->name,
			$tablename,
			implode(', ', $keys),
			implode(',', $_values)
		));
	}
	
	private function exec(string $sql) : void
	{
		try {
			$this->connection->exec($sql);
		}
		catch (\Exception $e) {
			trigger_error(sprintf('Could not process:  %s - %s', $sql, $e->getMessage()));
			throw $e;
		}
	}
	
}
