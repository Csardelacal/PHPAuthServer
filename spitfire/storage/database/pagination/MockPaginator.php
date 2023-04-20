<?php namespace spitfire\storage\database\pagination;

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

class MockPaginator implements PaginationInterface
{
	
	private $current;
	
	public function __construct($current = 1) {
		$this->current = $current;
	}

	public function after() {
		return '::after' . PHP_EOL;
	}

	public function before() {
		return '::before' . PHP_EOL;
	}

	public function current() {
		return $this->current;
	}

	public function emptyResultMessage() {
		return '::empty' . PHP_EOL;
	}

	public function first() {
		return '::first' . PHP_EOL;
	}

	public function last($number) {
		return '::last' . PHP_EOL;
	}

	public function next($disabled = false) {
		return '::next' . PHP_EOL;
	}

	public function page($number) {
		return '::page #' . $number . PHP_EOL;
	}

	public function previous($disabled = false) {
		return '::previous' . PHP_EOL;
	}

	public function gap() {
		return '::gap' . PHP_EOL;
	}

	public function jumpTo($total) {
		return '::jumpTo ' . $total . PHP_EOL;
	}

	public function pageOf($total) {
		return '::pageOf ' . $total . PHP_EOL;
	}

}
