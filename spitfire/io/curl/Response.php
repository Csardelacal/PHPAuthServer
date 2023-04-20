<?php namespace spitfire\io\curl;

use spitfire\exceptions\PrivateException;

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

class Response
{
	
	private $status;
	
	private $body;
	
	private $mime;
	
	public function __construct($status, $body, $mime) {
		$this->status = $status;
		$this->body = $body;
		$this->mime = explode(';', $mime)[0];
	}
	
	public function status() {
		return (int)$this->status;
	}
	
	public function expect($code) {
		if ($code !== $this->status) {
			var_dump($this->body);
			throw new BadStatusCodeException('Bad status code, ' . $this->status . ': ' . $this->body, 1805311227);
		}
		
		return $this;
	}
	
	public function html() {
		return $this->body;
	}
	
	public function mime() {
		return $this->mime;
	}
	
	public function json() {
		$parsed = json_decode($this->body);
		
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new PrivateException('Error while parsing JSON data: ' . json_last_error_msg() . $this->html(), 1805311215);
		}
		
		return $parsed;
	}
	
}
