<?php namespace tests\storage\drive;

use PHPUnit\Framework\TestCase;
use spitfire\storage\drive\Directory;
use spitfire\storage\drive\File;
use spitfire\storage\drive\MountPoint;
use spitfire\storage\objectStorage\DirectoryInterface;
use spitfire\storage\objectStorage\FileInterface;
use function storage;

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

class DriveTest extends TestCase
{
	
	private $storage;
	private $string = 'Hello world';
	
	public function setUp() {
		parent::setUp();
		
		$this->storage = storage();
		$this->storage->register(new MountPoint('tests://', sys_get_temp_dir()));
		
		storage('tests://')->contains('test') === DirectoryInterface::CONTAINS_DIR? null : storage('tests://')->mkdir('test');
		storage('tests://')->contains('temp.txt') === DirectoryInterface::CONTAINS_FILE? null : storage('tests://')->make('temp.txt')->write('Hello');
	}
	
	public function testOpenDrive() {
		$dir = $this->storage->get('tests://test');
		
		$this->assertInstanceOf(Directory::class, $dir);
		
		/*
		 * Test that the path, and URI are exactly the same when we retrieve the file
		 * via URI and via the object oriented interface
		 */
		$this->assertEquals($dir->uri(), storage('tests://test/')->uri());
		$this->assertEquals($dir->getPath(), storage('tests://test/')->getPath());
		$this->assertEquals($dir->uri(), 'tests://test/');
		
		return $dir;
	}
	
	/**
	 * 
	 * @depends testOpenDrive
	 * @param Directory $dir
	 */
	public function testCreateFile(Directory$dir) {
		$file = $dir->make('test.txt');
		$this->assertInstanceOf(FileInterface::class, $file);
		
		
		$file->write($this->string);
		$this->assertEquals($this->string, $file->read());
		
		return $file;
	}
	
	/**
	 * 
	 * @depends testCreateFile
	 * @param File $file
	 */
	public function testReadFile(File$file) {
		$uri  = 'tests://test/test.txt';
		$read = storage($uri);
		
		$this->assertEquals($uri, $read->uri());
		$this->assertInstanceOf(File::class, $read);
		$this->assertEquals($this->string, $read->read());
		
		return $file;
	}
	
	/**
	 * 
	 * @depends testReadFile
	 * @expectedException spitfire\exceptions\PrivateException
	 * @param File $file
	 */
	public function testDeleteFile(File$file) {
		$file->delete();
		$file->up()->open('test.txt');
	}
	
	public function testContains() {
		$this->assertEquals(DirectoryInterface::CONTAINS_DIR, storage('tests://')->contains('test'));
		$this->assertEquals(DirectoryInterface::CONTAINS_FILE, storage('tests://')->contains('temp.txt'));
		$this->assertEquals(DirectoryInterface::CONTAINS_NONX, storage('tests://')->contains('nada.file'));
	}
	
	public function tearDown() {
		parent::tearDown();
		
		$this->storage->unregister('tests://');
	}
}