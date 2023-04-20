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

class Console
{
	
	private $stdout;
	private $stderr;
	
	private $colors;
	
	private $current = null;
	
	public function __construct() {
		$this->stdout = new Stream(STDOUT);
		$this->stderr = new Stream(STDERR);
		
		$this->colors = new CLIColor();
	}
	
	public function error($msg) {
		$this->current = $this->stdout;
		$width = within(70, (int)exec('tput cols'), 200) - 7;
		$out = str_replace(PHP_EOL, PHP_EOL . '       ', trim(chunk_split($msg, $width, PHP_EOL)));
		
		$this->current
			->out('[')
			->out($this->colors->color(CLIColor::FG_RED))
			->out('FAIL')
			->out($this->colors->color(CLIColor::RESET))
			->out('] ')
			->out($out);
		
		return $this;
	}
	
	public function info($msg) {
		$this->current = $this->stdout;
		$width = within(70, (int)exec('tput cols'), 200) - 7;
		$out = str_replace(PHP_EOL, PHP_EOL . '       ', trim(chunk_split($msg, $width, PHP_EOL)));
		
		$this->current
			->out('[')
			->out($this->colors->color(CLIColor::FG_BLUE))
			->out('INFO')
			->out($this->colors->color(CLIColor::RESET))
			->out('] ')
			->out($out);
		
		return $this;
	}
	
	public function success($msg) {
		$this->current = $this->stdout;
		$width = within(70, (int)exec('tput cols'), 200) - 7;
		$out = str_replace(PHP_EOL, PHP_EOL . '       ', trim(chunk_split($msg, $width, PHP_EOL)));
		
		$this->current
			->out('[')
			->out($this->colors->color(CLIColor::FG_GREEN))
			->out(' OK ')
			->out($this->colors->color(CLIColor::RESET))
			->out('] ')
			->out($out);
		
		return $this;
	}
	
	public function progress($msg) {
		return $this->current = new ProgressBar($msg);
	}
	
	public function stdout() {
		return $this->stdout;
	}
	
	public function stderr() {
		return $this->stderr;
	}
	
	public function rewind() {
		$this->current && $this->current->rewind();
		return $this;
	}
	
	public function ln() {
		$this->current && $this->current->line();
		return $this;
	}
	
}
