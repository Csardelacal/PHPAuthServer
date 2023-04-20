<?php namespace tests\spitfire\storage\database\drivers\mysqlpdo;

use IntegerField;
use PHPUnit\Framework\TestCase;
use spitfire\exceptions\PrivateException;
use spitfire\storage\database\drivers\mysqlpdo\Driver;
use spitfire\storage\database\Schema;
use spitfire\storage\database\Settings;
use spitfire\storage\database\Table;
use StringField;

class QueryTest extends TestCase
{
	
	private $db;
	
	/**
	 * The table we're testing.
	 *
	 * @var Table
	 */
	private $table;
	private $schema;
	
	public function setUp() {
		//Just in case Mr. Bergmann decides to add code to the setUp
		parent::setUp();
		
		try {
			$this->db = new Driver(Settings::fromArray([]));
			$this->db->create();

			$this->schema = new Schema('test');

			$this->schema->field1 = new IntegerField(true);
			$this->schema->field2 = new StringField(255);

			$this->table = new Table($this->db, $this->schema);
			$this->table->getLayout()->create();
		}
		catch (PrivateException$e) {
			$this->markTestSkipped('MySQL PDO driver is not available.');
		}
	}
	
	public function tearDown() {
		$this->db->destroy();
	}
	
	public function testQuery() {
		$record = $this->table->newRecord();
		$record->field1 = 1;
		$record->field2 = 'Test';
		$record->store();
		
		$result = $this->table->get('field1', 1)->fetch();
		$this->assertNotEquals(null, $result);
	}
	
}
