<?php namespace spitfire\io\template;

use spitfire\exceptions\PrivateException;
use spitfire\exceptions\FileNotFoundException;

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

class Template
{
	
	private $file;
	
	private $sections;
	
	public function __construct($file) {
		$this->file = (array)$file;
	}
	
	public function renderable() {
		
		foreach ($this->file as $file) {
			if (file_exists($file)) { return $file; }
		}
		
		return false;
	}
	
	public function setFile($file) {
		$this->file = (array)$file;
		return $this;
	}
	
	public function section($name, Template$set = null) {
		if ($set) {
			$this->sections[$name] = $set;
			return $this;
		}
		
		if (isset($this->sections[$name])) {
			return $this->sections[$name];
		}
		
		throw new PrivateException('Section ' . $name . ' not available', 1806011432);
	}
	
	public function render($__data) {
		#Consider that a missing template file that should be rendered is an error
		if (!$__file = $this->renderable()) { 
			throw new FileNotFoundException('No valid template file provided', 1806011423);
		}
		
		ob_start();
		
		foreach ($__data as $__var => $__content) {
			$$__var = $__content;
		}
		
		include $__file;
		
		return ob_get_clean();
	}
	
}
