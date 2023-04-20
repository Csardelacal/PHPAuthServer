<?php namespace tests\spitfire\storage\db;

use IntegerField;
use PHPUnit\Framework\TestCase;
use spitfire\model\Field as Field2;
use spitfire\storage\database\drivers\mysqlpdo\Field as MysqlField;
use spitfire\storage\database\Field;
use spitfire\storage\database\Schema;
use spitfire\storage\database\Settings;
use spitfire\storage\database\Table;
use StringField;
use function db;

class TableTest extends TestCase
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
		
		//TODO: This needs to be replaced with logic that actually is properly testable.
		//Currently there is no DB mock driver. Not sure if I should create one or just test different drivers
		$this->db = db(Settings::fromArray(['schema' => 'test_schema']));
		
		$this->schema = new Schema('test');
		
		$this->schema->field1 = new IntegerField(true);
		$this->schema->field2 = new StringField(255);
		
		$this->table = new Table($this->db, $this->schema);
	}
	
	public function testGetField() {
		$this->assertInstanceOf(Field::class, $this->table->getLayout()->getField('field1'));
		$this->assertInstanceOf(Field::class, $this->table->getLayout()->getField('field2'));
		
		//This checks that the table identifies and returns when an object is provided
		$this->assertInstanceOf(Field::class, $this->table->getLayout()->getField($this->table->getLayout()->getField('field2')));
	}
	
	/**
	 * @expectedException spitfire\exceptions\PrivateException
	 */
	public function testGetUnexistingFieldByName() {
		$this->table->getLayout()->getField('unexistingfield');
	}
	
	/**
	 * @expectedException spitfire\exceptions\PrivateException
	 */
	public function testGetUnexistingFieldByObject() {
		$schema = new Schema('test\storage\database\Table\notreal');
		$this->db->table($schema);
		$schema->field = new IntegerField();
		$this->table->getLayout()->getField(new MysqlField($schema->field, 'notexisting'));
	}


	public function testFieldTypes() {
		$this->assertEquals(Field2::TYPE_STRING, $this->table->getLayout()->getField('field2')->getLogicalField()->getDataType());
	}
	
}
