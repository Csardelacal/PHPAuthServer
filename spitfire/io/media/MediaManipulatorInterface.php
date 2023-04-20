<?php namespace spitfire\io\media;

use spitfire\storage\objectStorage\FileInterface;

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

interface MediaManipulatorInterface
{
	
	const WIDTH = 0;
	
	const HEIGHT = 1;
	
	const QUALITY_MINIMAL  = 0;
	const QUALITY_LOW      = 1;
	const QUALITY_MEDIUM   = 2;
	const QUALITY_HIGH     = 3;
	const QUALITY_VERYHIGH = 4;
	const QUALITY_OPTIMAL  = 5;
	
	public function supports(string$mime) : bool;
	
	public function load(FileInterface$blob) : MediaManipulatorInterface;
	
	public function store(FileInterface$location) : FileInterface;
	
	public function fit($x, $y) : MediaManipulatorInterface;
	
	public function scale($target, $side = MediaManipulatorInterface::WIDTH) : MediaManipulatorInterface;
	
	public function blur() : MediaManipulatorInterface;
	
	public function grayscale() : MediaManipulatorInterface;
	
	public function quality($target = MediaManipulatorInterface::QUALITY_VERYHIGH) : MediaManipulatorInterface;
	
	public function background($r, $g, $b, $alpha = 0): MediaManipulatorInterface;
	
	public function poster(): MediaManipulatorInterface;
	
	public function dimensions();
}
