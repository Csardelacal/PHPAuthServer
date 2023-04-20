<?php namespace spitfire\storage\drive;

use BadMethodCallException;
use spitfire\exceptions\FileNotFoundException;
use spitfire\storage\objectStorage\DirectoryInterface;
use spitfire\storage\objectStorage\MountPointInterface;
use spitfire\storage\objectStorage\NodeInterface;

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

class MountPoint extends Directory implements MountPointInterface
{
	
	
	/**
	 * The URI scheme used to identify the drive. This works much like URI schemes
	 * or the drive letters on windows machines, except that it does not restrict
	 * the amount of characters it can use.
	 *
	 * @var string
	 */
	private $scheme;
	
	/**
	 * The entry point to this drive. This is the root mount of the virtual filesystem
	 * and should be able to retrieve the data appropriately. This  may contain the
	 * login settings for cloud based storage.
	 *
	 * @var string
	 */
	private $root;
	
	/**
	 * Create a new virtual drive to bind a physical location to a virtual scheme.
	 * Your application can therefore create a virtual drive like uploads:// to
	 * link to your upload directory.
	 * 
	 * This makes the task of system administration and, for example, moving a directory
	 * to a new location faster, since you just need to update the root of the
	 * virtual drive.
	 * 
	 * @param string $scheme
	 * @param string $root
	 */
	public function __construct($scheme, $root) {
		if (!is_dir($root)) {
			throw new FileNotFoundException('Mount point does not exist', 1808121224);
		}
		
		$this->scheme = trim($scheme, ':/');
		$this->root = rtrim($root, '\/') . DIRECTORY_SEPARATOR;
		
		parent::__construct($this, null);
	}
	
	/**
	 * Retrieve the scheme the virtual drive, when provided the scheme, the drive
	 * manager will link all the requests for this scheme to the drive.
	 * 
	 * @return string
	 */
	public function scheme() {
		return $this->scheme;
	}
	
	public function uri(): string {
		return trim($this->scheme, ':/') . '://';
	}
	
	public function getPath() {
		return $this->root;
	}

}
