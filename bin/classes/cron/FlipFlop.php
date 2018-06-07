<?php namespace cron;

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

class FlipFlop
{
	
	private $queue;
	
	public function __construct($filename) {
		
		if (!function_exists('msg_get_queue')) {
			throw new PrivateException('Flip-flop requires SystemV Semaphores to work', 1806061928);
		}
		
		if (!file_exists($filename)) {
			fclose(fopen($filename, 'w'));
		}
		
		$this->queue = msg_get_queue(ftok($filename, 1));
	}
	
	public function notify() {
		$type = 0;
		$message = '';
		
		msg_receive($this->queue, 0, $type, 4096, $message, true, MSG_IPC_NOWAIT);
		msg_send($this->queue, 1, time());
	}
	
	public function wait() {
		$type = 0;
		$message = '';
		
		$success = msg_receive($this->queue, 0, $type, 4096, $message, true);
		
		return $success? $message : false;
	}
	
}
