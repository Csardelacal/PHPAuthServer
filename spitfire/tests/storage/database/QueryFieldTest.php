<?php namespace tests\storage\database;

use IntegerField;
use Reference;
use spitfire\storage\database\drivers\mysqlpdo\QueryField;
use spitfire\storage\database\drivers\mysqlpdo\QueryTable;
use spitfire\storage\database\Schema;
use spitfire\storage\database\Settings;
use spitfire\storage\database\Table;
use function db;

/* 
 * The MIT License
 *
 * Copyright 2018 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class QueryFieldTest extends \PHPUnit\Framework\TestCase
{
	
	public function testGetPhysical() {
		
		$schema  = new Schema('test');
		
		unset($schema->_id);
		$schema->test  = new IntegerField();
		$schema->test2 = new IntegerField();
		
		$schema->index($schema->test, $schema->test2)->setPrimary(true);
		
		$field = new Reference($schema);
		$field->getTarget();
		
		$db = db(Settings::fromArray(['schema' => 'test_schema']));
		$qf = new QueryField(new QueryTable(new Table($db, $schema)), $field);
		
		
		$result = $qf->getPhysical();
		
		$this->assertCount(2, $result);
	}
	
}