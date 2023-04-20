<?php namespace tests\spitfire\storage\database;

use PHPUnit\Framework\TestCase;

/* 
 * The MIT License
 *
 * Copyright 2017 César de la Cal Bretschneider <cesar@magic3w.com>.
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

class SettingsTest extends TestCase
{
	
	public function testMakeFromURL() {
		$settings = \spitfire\storage\database\Settings::fromURL('postgres://root:pass@db1.test.com:8080/database?prefix=test_&encoding=utf8');
		
		$this->assertEquals('postgres', $settings->getDriver());
		$this->assertEquals('root'    , $settings->getUser());
		$this->assertEquals('pass'    , $settings->getPassword());
		$this->assertEquals('database', $settings->getSchema());
		$this->assertEquals('utf8',     $settings->getEncoding());
	}
	
	public function testMakeFromURLRelativePath() {
		$settings = \spitfire\storage\database\Settings::fromURL('sqlite3://root:pass@db1.test.com:8080/relative/path.sqlite?encoding=latin1');
		$this->assertEquals('sqlite3' , $settings->getDriver());
		$this->assertEquals('relative/path.sqlite', $settings->getSchema());
		$this->assertEquals('latin1', $settings->getEncoding());
	}
	
	public function testMakeFromURLAbsolutePath() {
		$settings = \spitfire\storage\database\Settings::fromURL('sqlite3://root:pass@db1.test.com:8080//absolute/path.sqlite');
		$this->assertEquals('/absolute/path.sqlite', $settings->getSchema());
	}
	
}