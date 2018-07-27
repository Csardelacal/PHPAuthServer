<?php namespace hook;

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

class Hook
{
	
	private $endpoint;
	
	/**
	 *
	 * @var \signature\Signature
	 */
	private $signature;
	
	public function __construct($endpoint, $signature) {
		$this->endpoint = $endpoint;
		$this->signature = $signature;
	}
	
	public function authenticate() {
		$request = request(rtrim($this->endpoint, '/') . '/app/authenticate.json');
		$request->get('signature', (string) $this->signature);
		
		
		return $request->send()->expect(200)->json();
	}
	
	public function on($src, $target) {
		$request = request(rtrim($this->endpoint, '/') . '/listener/on/' . $src . '.json');
		$request->get('signature', (string)$this->signature);
		$request->get('target', $target);
		
		return $request->send()->expect(200)->json();
	}
	
	public function listeningFor($appId) {
		$request = request(rtrim($this->endpoint, '/') . '/listener/target/' . $appId . '.json');
		$request->get('signature', (string)$this->signature);
		
		return $request->send()->expect(200)->json();
	}
	
	public function trigger($event, $payload = null, $defer = null) {
		
		if ($defer > time()) { 
			$schedule = $defer;
			$defer = null;
		}
		else {
			$schedule = null;
		}
		
		$request = request(rtrim($this->endpoint, '/') . '/listener/trigger.json');
		$request->get('event', $event);
		$request->get('defer', $defer);
		$request->get('schedule', $schedule);
		$request->get('signature', (string)$this->signature);
		
		$request->header('Content-type', 'application/json');
		$request->post(json_encode($payload));
		
		$response = $request->send()->expect(200)->json();
		
		return $response;
	}
	
	public function register($src, $tgt, $id, $event, $url, $defer = 0, $format = 'json') {
		$request = request(rtrim($this->endpoint, '/') . '/listener/register.json');
		$request->get('signature', (string)$this->signature);
		
		$request->post('app', $src);
		$request->post('target', $tgt);
		$request->post('hid', $id);
		$request->post('listen', $event);
		$request->post('url', $url);
		$request->post('defer', $defer);
		$request->post('format', $format);
		
		return $request->send()->expect(200)->json();
	}
	
}