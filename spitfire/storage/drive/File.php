<?php namespace spitfire\storage\drive;

use spitfire\io\stream\StreamSourceInterface;
use spitfire\storage\objectStorage\DirectoryInterface;
use spitfire\storage\objectStorage\FileInterface;
use spitfire\storage\objectStorage\NodeInterface;
use function mime;

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

class File implements FileInterface, StreamSourceInterface, \spitfire\io\stream\StreamTargetInterface
{
	
	private $parent;
	
	private $path;
	
	public function __construct($_1, $_2 = null) {
		$this->parent = $_2 === null? null : $_1;
		$this->path = $_2 === null? $_1 : $_2;
	}
	
	public function delete(): bool {
		return unlink($this->getPath());
	}
	
	public function getPath() {
		return $this->up()->getPath() . $this->path;
	}

	public function up(): NodeInterface {
		return $this->parent;
	}

	public function isWritable(): bool {
		return (file_exists($this->getPath()) && is_writable($this->getPath())) || $this->up()->isWritable();
	}

	public function move(DirectoryInterface $to, string $name): FileInterface {
		/*
		 * If the target is a directory we can directly move the file on the drive,
		 * therefore we don't have to get the file from the drive a second time.
		 */
		if ($to instanceof Directory) { rename($this->getPath(), $to->get($name)->getPath()); }
		else                          { $to->get($name)->write($this->read()); }
		
		return $to->get($name);
	}
	
	public function read(): string {
		return file_get_contents($this->getPath());
	}
	
	public function write(string $data): bool {
		return file_put_contents($this->getPath(), $data);
	}

	public function uri() : string {
		return $this->up()->uri() . $this->path;
	}

	public function mime(): string {
		
		$lib = [
			'jpeg' => 'image/jpeg',
			'jpg'  => 'image/jpeg',
			'png'  => 'image/png',
			'gif'  => 'image/gif',
			'psd'  => 'image/psd',
			'mp4'  => 'video/mp4'
		];
		
		return mime($this->getPath())? : $lib[pathinfo($this->getPath(), PATHINFO_EXTENSION)];
	}

	public function basename(): string {
		return pathinfo($this->getPath(), PATHINFO_BASENAME);
	}

	public function filename(): string {
		return pathinfo($this->getPath(), PATHINFO_FILENAME);
	}

	public function getStreamReader(): \spitfire\io\stream\StreamReaderInterface {
		return new FileStreamReader($this->getPath());
	}

	public function getStreamWriter() : \spitfire\io\stream\StreamWriterInterface {
		return new FileStreamWriter($this->getPath());
	}

}
