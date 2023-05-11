<?php namespace commishes\qa\runner\fixtures;

use PDO;
use SimpleXMLElement;

class Database
{
	
	private PDO $connection;
	
	/**
	 * 
	 * @todo Add logger interface so database queries can be logged
	 * @param PDO $connection
	 */
	public function __construct(PDO $connection)
	{
		$this->connection = $connection;
	}
	
	public function loadFromXML(string $filename) : void
	{
		$document = simplexml_load_file($filename);
		
		assert($document !== false);
		assert($document->getName() === 'fixture');
		
		$schemas = $document->children();
		foreach ($schemas as $schema) {
			$this->schema($schema);
		}
	}
	
	private function schema(SimpleXMLElement $document) : void
	{
		assert($document->getName() === 'schema');
		
		$processor = new SchemaProcessor($this->connection, $document->attributes()['name']);
		$tables = $document->children();
		
		foreach ($tables as $table) {
			$processor->process($table);
		}
	}
	
	public function query($sql) : array
	{
		$res = $this->connection->query($sql);
		return $res->fetchAll(PDO::FETCH_ASSOC);
	}
	
}
