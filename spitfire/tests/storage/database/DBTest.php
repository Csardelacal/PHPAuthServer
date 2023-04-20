<?php namespace tests\spitfire\storage\db;

use PHPUnit\Framework\TestCase;
use spitfire\core\Environment;
use spitfire\storage\database\drivers\mysqlpdo\Driver;
use spitfire\storage\database\Settings;
use function db;

class DBTest extends TestCase
{
	
	public function testdb() {
		$this->assertInstanceOf('spitfire\storage\database\DB', db());
	}
	
	public function testTableCache() {
		$db = new Driver(Settings::fromURL(Environment::get('db')));
		$db->table(new \spitfire\storage\database\Schema('test'));
		
		$tc = $db->getTableCache();
		$this->assertInstanceOf(\spitfire\storage\database\Table::class, $tc->get('test'));
	}
		
}