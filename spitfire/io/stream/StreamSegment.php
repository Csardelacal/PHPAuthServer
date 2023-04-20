<?php namespace spitfire\io\stream;

use spitfire\exceptions\OutOfBoundsException;

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
 * The stream segment class allows an application to restrict access to a stream
 * to a specific segment of it.
 * 
 * This class comes in handy when doing HTTP range requests where the sender 
 * determines a segment of a file that it wishes to receive.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class StreamSegment implements StreamReaderInterface, SeekableStreamInterface
{
	
	/**
	 *
	 * @var StreamReaderInterface
	 */
	private $src;
	
	/**
	 * Determines the first index of the stream that should be returned when
	 * reading from this segment.
	 *
	 * @var int 
	 */
	private $start;
	
	/**
	 * The last index to be read when the application is trying to read the source
	 * stream.
	 *
	 * @var int|null
	 */
	private $end;
	
	/**
	 * 
	 * @param SeekableStreamInterface $src
	 * @param int $start
	 * @param int $end
	 * @throws OutOfBoundsException
	 */
	public function __construct(SeekableStreamInterface$src, $start, $end = null) {
		$this->src = $src;
		$this->start = $start;
		$this->end = $end;
		
		if ($this->end && $this->start >= $this->end) {
			throw new OutOfBoundsException('Start of stream segment is out of bounds', 1811081804);
		}
		
		$this->src->seek($this->start);
	}
	
	/**
	 * 
	 * @todo check if this method is not indeed bugged
	 * @return int
	 */
	public function length(): int {
		if ($this->end) {
			return $this->end - $this->start + 1;
		}
		else {
			return $this->src->length() - $this->start;
		}
	}
	
	/**
	 * 
	 * @param int $length
	 * @return string
	 */
	public function read($length = null) {
		
		if ($this->end) {
			$max = $this->end - $this->src->tell() + 1;
			
			if ($max <= 0) {
				return '';
			}
			
			$read = $this->src->read($length);
			
			if (isset($read[$max])) {
				return substr($read, 0, $max);
			}
			else {
				return $read;
			}
		}
		
		else {
			return $this->src->read($length);
		}
		
		
	}
	
	/**
	 * 
	 * @param int $position
	 * @return \spitfire\io\stream\StreamInterface
	 */
	public function seek($position): StreamInterface {
		$this->src->seek($position + $this->start);
		
		return $this;
	}
	
	/**
	 * 
	 * @return int
	 */
	public function tell(): int {
		return $this->src->tell() - $this->start;
	}

}