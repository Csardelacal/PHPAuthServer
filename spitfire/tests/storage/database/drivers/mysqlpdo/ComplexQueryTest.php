<?php namespace tests\spitfire\storage\database\drivers\mysqlpdo;

use IntegerField;
use PHPUnit\Framework\TestCase;
use Reference;
use spitfire\exceptions\PrivateException;
use spitfire\storage\database\drivers\mysqlpdo\Driver;
use spitfire\storage\database\Schema;
use spitfire\storage\database\Settings;
use spitfire\storage\database\Table;
use StringField;

class ComplexQueryTest extends TestCase
{
	
	private $db;
	
	/**
	 * The table we're testing.
	 *
	 * @var Table
	 */
	private $table;
	private $schema;
	
	/**
	 * The table we're testing.
	 *
	 * @var Table
	 */
	private $table2;
	private $schema2;
	
	/**
	 * The table we're testing.
	 *
	 * @var Table
	 */
	private $table3;
	private $schema3;
	
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

			$this->schema2 = new Schema('test2');

			$this->schema2->refer  = new Reference($this->schema);
			$this->schema2->field1 = new IntegerField(true);
			$this->schema2->field2 = new StringField(255);

			$this->table2 = new Table($this->db, $this->schema2);
			$this->table2->getLayout()->create();

			$this->schema3 = new Schema('test3');
			
			$this->schema3->refer  = new Reference($this->schema2);
			$this->schema3->field1 = new IntegerField(true);
			$this->schema3->field2 = new StringField(255);

			$this->table3 = new Table($this->db, $this->schema3);
			$this->table3->getLayout()->create();
		}
		catch (PrivateException$e) {
			$this->markTestSkipped('MySQL PDO driver is not available.');
		}
	}
	
	public function tearDown() {
		$this->db->destroy();
	}
	
	public function testQuery() {
		$q1 = $this->table->get('field1', 1);
		$q2 = $this->table2->get('refer', $q1);
		$q3 = $this->table3->get('refer', $q2);
		
		$this->assertInstanceOf(\spitfire\collection\Collection::class, $q3->fetchAll());
		$this->assertEquals(0, $q3->count());
	}
	
}
