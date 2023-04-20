<?php namespace spitfire\io\cli;

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

class ProgressBar extends Stream
{
	
	private $msg;
	private $progress = 0;
	private $lastredraw = 0;
	
	public function __construct($msg) {
		parent::__construct();
		
		$this->msg = $msg;
		$this->redraw();
	}
	
	public function progress($progress) {
		$this->progress = $progress;
		$this->redraw();
		return $this;
	}
	
	public function redraw() {
		if (time() === $this->lastredraw) { return; }
		
		$this->lastredraw = time();
		$this->rewind();
		
		if ($this->progress < 0 || $this->progress > 1) {
			$this->out(sprintf('[WAIT] %s [%s]', $this->msg, 'Invalid value ' . $this->progress));
		}
		else {
			$width = exec('tput cols') - strlen($this->msg) - 10;
			$drawn = (int)($this->progress * $width);
			$this->out(sprintf('[WAIT] %s [%s%s]', $this->msg, str_repeat('#', $drawn), str_repeat(' ', $width - $drawn)));
		}
	}
}
