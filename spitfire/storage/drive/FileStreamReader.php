<?php namespace spitfire\storage\drive;

use spitfire\exceptions\FilePermissionsException;
use spitfire\io\stream\SeekableStreamInterface;
use spitfire\io\stream\StreamInterface;
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

class FileStreamReader implements StreamReaderInterface, SeekableStreamInterface
{
	
	/**
	 * The file handle used to stream from the drive to the application. If the 
	 * application was unable to open the stream reading it will yield a 
	 * file permissions exception.
	 *
	 * @var resource|false
	 */
	private $fh;
	
	private $path;
	
	/*
	 * Instance a new FileStreamReader. This allows the application to read chunks
	 * of a file to memory and process them in batch.
	 */
	public function __construct($path) {
		$this->path = $path;
		$this->fh = fopen($path, 'r');
	}
	
	/**
	 * Reads up to a given amount of bytes from the file. Please note that if the
	 * file has been completely read or it's length is shorter than the amount provided,
	 * it will return the entire remaining file.
	 * 
	 * The standard amount to be read is 8MB.
	 * 
	 * @throws FilePermissionsException
	 * @return string
	 */
	public function read($length = null) {
		
		if ($this->fh === false) {
			throw new FilePermissionsException('Cannot read file to stream', 1810020915);
		}
		
		return fread($this->fh, $length?: 4 * 1024 * 1024);
	}
	
	/**
	 * Moves the file read pointer to the given address. The offset is given in
	 * bytes from the start of the file.
	 * 
	 * @param int $position
	 * @return StreamInterface
	 * @throws FilePermissionsException
	 */
	public function seek($position): StreamInterface {
		
		if ($this->fh === false) {
			throw new FilePermissionsException('Cannot read file to stream', 1810020915);
		}
		
		fseek($this->fh, $position);
		return $this;
	}
	
	
	public function tell(): int {
		return ftell($this->fh);
	}
	
	public function length(): int {
		return filesize($this->path);
	}


}
