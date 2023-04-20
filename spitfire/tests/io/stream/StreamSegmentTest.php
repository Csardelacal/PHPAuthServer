<?php namespace tests\spitfire\io\stream;

use PHPUnit\Framework\TestCase;

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

class StreamSegmentTest extends TestCase
{
	
	private $file;
	
	public function setUp() {
		$this->file = storage()->dir('file://' . sys_get_temp_dir())->make('php' . uniqid());
		$this->file->write('Hello world!');
	}
	
	public function testSegment() {
		$segment = new \spitfire\io\stream\StreamSegment($this->file->getStreamReader(), 6, 8);
		$read = $segment->read();
		
		$this->assertEquals(3, strlen($read));
		$this->assertEquals(3, $segment->length());
		$this->assertEquals('wor', $read);
	}
	
	public function testSegment2() {
		$segment = new \spitfire\io\stream\StreamSegment($this->file->getStreamReader(), 6);
		$read = $segment->read();
		
		$this->assertEquals(6, strlen($read));
		$this->assertEquals(6, $segment->length());
		$this->assertEquals('world!', $read);
	}
	
	public function tearDown() {
		$this->file->delete();
	}
	
}