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

/**
 * This interface allows applications to quickly implement custom user interfaces
 * while maintaining the behavior of the standard pagination system.
 * 
 * @todo Find a better name for this
 * @todo Document the different methods
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
interface PaginationInterface
{
	/**
	 * Returns the current page 
	 * 
	 * @return int
	 */
	public function current();
	
	public function emptyResultMessage();
	
	public function page($number);
	
	public function previous($disabled = false);
	public function next($disabled = false);
	
	public function first();
	public function last($number);
	
	public function before();
	public function after();
	
	public function gap();
	public function jumpTo($total);
	public function pageOf($total);
	
}
