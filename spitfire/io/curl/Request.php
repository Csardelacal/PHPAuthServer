<?php namespace spitfire\io\curl;

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

class Request
{
	
	private $method = 'GET';
	
	private $url;
	
	private $headers = [];
		
	private $body = [];
	
	private $follow = true;
	
	private $stream = false;
	
	public function __construct($url) {
		$this->url = URLReflection::fromURL($url);
	}
	
	public function url($url) {
		$this->url = URLReflection::fromURL($url);
	}
	
	public function header($name, $value) {
		$this->headers[$name] = $value;
		return $this;
	}
	
	public function follow($set = true) {
		$this->follow = !!$set;
	}
	
	public function get($parameter, $value = null) {
		$qs = $this->url->getQueryString();
		$qs[$parameter] = $value;
		$this->url->setQueryString($qs);
		
		return $this;
	}
	
	public function post($parameter, $value = null) {
		$this->method = 'POST';
		
		if ($value === null) {
			$this->body = $parameter;
		}
		else {
			$this->body[$parameter] = $value;
		}
		
		return $this;
	}
	
	public function stream($callable) {
		$this->stream = function ($ign, $d)  use ($callable) { return (int)$callable($d); };
		return $this;
	}
	
	public function send($progress = null) {
		$ch = curl_init((string)$this->url);
		
		if ($this->stream) {
			curl_setopt($ch, CURLOPT_WRITEFUNCTION, $this->stream);
		}
		else {
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		}
		
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		
		if (!empty($this->body)) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body);
		}
		
		/*
		 * Set the appropriate headers for the request (in case we need some special
		 * headers that do not conform)
		 */
		curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function ($k, $v) { 
			return "{$k}:{$v}"; 
		}, array_keys($this->headers), $this->headers));
		
		$progress && curl_setopt($ch, CURLOPT_NOPROGRESS, false);
		$progress && curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, $progress);
		
		$this->follow && curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->follow);
		
		$_ret = curl_exec($ch);
		$meta = curl_getinfo($ch);
		
		return new Response($meta['http_code'], $_ret, $meta['content_type']);
	}
}
