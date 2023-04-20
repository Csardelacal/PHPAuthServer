<?php namespace spitfire\storage\drive;

use spitfire\io\stream\StreamReaderInterface;

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

class FileStreamWriter implements \spitfire\io\stream\StreamWriterInterface
{
	
	/**
	 * The file handle used to stream from the drive to the application. If the 
	 * application was unable to open the stream reading it will yield a 
	 * file permissions exception.
	 *
	 * @var resource|false
	 */
	private $fh;
	
	public function __construct($path) {
		$this->fh = fopen($path, 'w+');
	}
	

	public function seek($position): \spitfire\io\stream\StreamInterface {
		
		if ($this->fh === false) {
			throw new FilePermissionsException('Cannot read file to stream', 1810020915);
		}
		
		fseek($this->fh, $position);
		return $this;
	}

	public function write($string) {
		
		if ($this->fh === false) {
			throw new FilePermissionsException('Cannot read file to stream', 1810020915);
		}
		
		return fwrite($this->fh, $string);
	}

}
