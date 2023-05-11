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
			}
		}
	}
	
	private function truncate(string $tablename)
	{
		$this->exec(sprintf('TRUNCATE %s', $this->name . '.' . $tablename));			
	}
	
	private function insert(string $tablename, array $values) : void
	{
		$_values = array_map(
			fn($v) => $this->connection->quote($v),
			$values
		);
		
		$this->exec(sprintf(
			'INSERT INTO %s (%s) VALUES (%s)',
			$this->name . '.' . $tablename,
			implode(', ', array_keys($values)),
			implode(',', $_values)
		));
	}
	
	private function exec(string $sql) : void
	{
		try {
			$this->connection->exec($sql);
		}
		catch (\Exception $e) {
			trigger_error(sprintf('Could not process:  %s', $sql));
			throw $e;
		}
	}
	
}
