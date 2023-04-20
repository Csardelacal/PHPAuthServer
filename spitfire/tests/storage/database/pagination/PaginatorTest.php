<?php namespace tests\spitfire\storage\database\pagination;

use IntegerField;
use PHPUnit\Framework\TestCase;
use spitfire\exceptions\PrivateException;
use spitfire\storage\database\drivers\mysqlpdo\Driver;
use spitfire\storage\database\pagination\MockPaginator;
use spitfire\storage\database\pagination\Paginator;
use spitfire\storage\database\Schema;
use spitfire\storage\database\Settings;
use spitfire\storage\database\Table;
use StringField;

class PaginatorTest extends TestCase
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
	
	/**
	 * 
	 * @todo Make this test MySQL independent. Right now it only tests whether MySQL works fine
	 * @todo Think of something smarter for the mock element
	 * 
	 * @covers spitfire\storage\database\pagination\Paginator::getPageCount
	 * @covers spitfire\storage\database\pagination\Paginator::records
	 */
	public function testPagination() {
		
		for ($i = 0; $i < 40; $i++) {
			$record = $this->table->newRecord();
			$record->field1 = $i;
			$record->field2 = 'Test';
			$record->store();
		}
		
		$query = $this->table->getAll();
		$paginator = new Paginator($query, new MockPaginator(1));
		
		$this->assertEquals(20, $paginator->records()->count());
		$this->assertEquals( 2, $paginator->getPageCount());
		$this->assertCount ( 2, $paginator->pages()->toArray());
	}
	
}
