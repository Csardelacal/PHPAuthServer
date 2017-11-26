<?php namespace auth;

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

class Context
{
	
	private $defined;
	private $id;
	private $appid;
	private $name;
	private $description;
	private $expires;
	
	
	public function __construct($defined, $id, $appid, $name, $description, $expires) {
		$this->defined = $defined;
		$this->id = $id;
		$this->appid = $appid;
		$this->name = $name;
		$this->description = $description;
		$this->expires = $expires;
	}
	
	public function getDefined() {
		return $this->defined;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getAppid() {
		return $this->appid;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function getExpires() {
		return $this->expires;
	}
	
	public function setDefined($defined) {
		$this->defined = $defined;
		return $this;
	}
	
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	
	public function setAppid($appid) {
		$this->appid = $appid;
		return $this;
	}
	
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}
	
	public function setExpires($expires) {
		$this->expires = $expires;
		return $this;
	}
}