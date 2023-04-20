<?php namespace spitfire\io\media;

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

class GDManipulator implements MediaManipulatorInterface
{
	
	private $tmp;
	private $meta;
	private $compression = 8;
	
	public function blur(): MediaManipulatorInterface {
		imagefilter($this->img, IMG_FILTER_SELECTIVE_BLUR);
		return $this;
	}

	public function fit($x, $y): MediaManipulatorInterface {
		$wider = ($this->meta[0] / $x) > ($this->meta[1] / $y);
		
		if ($wider) {
			$ratio    = $this->meta[1] / $y;
			$offset_x = ($this->meta[0] - $x * $ratio) / 2;
			$offset_y = 0;
		}
		else {
			$ratio    = $this->meta[0] / $x;
			$offset_y = ($this->meta[1] - $y * $ratio) / 2;
			$offset_x = 0;
		}

		if ($offset_x == 0 && $offset_y == 0){
			$x = min($this->meta[0], $x);
			$y = min($this->meta[1], $y);
		}
		
		$img = imagecreatetruecolor($x, $y);
		imagecolortransparent($img , imagecolorallocatealpha($img , 255, 255, 255, 127));
		imagealphablending($img, false);
		imagesavealpha($img, true);
		imagecopyresampled($img, $this->img, 0, 0, $offset_x, $offset_y, $x, $y, $this->meta[0]-2*$offset_x, $this->meta[1]-2*$offset_y);
		$this->img = $img;
		
		$this->meta[0] = $x;
		$this->meta[1] = $y;
		
		return $this;
	}

	public function grayscale(): MediaManipulatorInterface {
		imagefilter($this->img, IMG_FILTER_GRAYSCALE);
		return $this;
	}

	public function load(\spitfire\storage\objectStorage\FileInterface $blob): MediaManipulatorInterface {
		
		if ($this->tmp) {
			unlink($this->tmp);
		}
		
		$this->tmp = '/tmp/' . rand();
		file_put_contents($this->tmp, $blob->read());
		

		$this->meta = getimagesize($this->tmp);

		if (!function_exists('imagecreatefrompng')) {
			throw new \spitfire\exceptions\PrivateException("GD is not installed.", 1805301100);
		}
		
		switch($this->meta[2]) {
			case IMAGETYPE_PNG: 
				$this->img = imagecreatefrompng($this->tmp);
				imagealphablending($this->img, false);
				imagesavealpha($this->img, true);
				break;
			case IMAGETYPE_JPEG:  
				$this->img = imagecreatefromjpeg($this->tmp);
				break;
			case IMAGETYPE_GIF: 
				$this->img = imagecreatefromgif($this->tmp);
				break;
			default:
				throw new \spitfire\exceptions\PrivateException('Unsupported image type: ' . $this->meta[2]);
		}
		
		return $this;
	}

	public function quality($target = MediaManipulatorInterface::QUALITY_VERYHIGH): MediaManipulatorInterface {
		//TODO: Implement
	}
	
	public function background($r, $g, $b, $alpha = 0): MediaManipulatorInterface {
		
		
		$img = imagecreatetruecolor($this->meta[0], $this->meta[1]);
		imagecolortransparent($img , imagecolorallocatealpha($img , 255, 255, 255, 127));
		imagealphablending($img, true);
		imagesavealpha($img, true);
		
		$bgcolor = imagecolorallocatealpha($img, $r, $g, $b, $alpha);
		imagefilledrectangle($img, 0, 0, $this->meta[0], $this->meta[1], $bgcolor);
		
		imagecopy($img, $this->img, 0, 0, 0, 0, $this->meta[0], $this->meta[1]);
		$this->img = $img;
		
		return $this;
	}

	public function scale($target, $side = MediaManipulatorInterface::WIDTH): MediaManipulatorInterface {
		
		
		if ($side === MediaManipulatorInterface::HEIGHT) {
			$height = $target;
			$width = $this->meta[0] * $target / $this->meta[1];
		}
		
		if ($side === MediaManipulatorInterface::WIDTH) {
			$width = $target;
			$height = $this->meta[1] * $target / $this->meta[0];
		}
		
		$img = imagecreatetruecolor($width, $height);
		imagecolortransparent($img , imagecolorallocatealpha($img , 0, 0, 0, 127));
		imagealphablending($img, false);
		imagesavealpha($img, true);
		imagecopyresampled($img, $this->img, 0, 0, 0, 0, $width, $height, $this->meta[0], $this->meta[1]);
		$this->img = $img;
		
		return $this;
	}

	public function store(\spitfire\storage\objectStorage\FileInterface $location): \spitfire\storage\objectStorage\FileInterface {
		
		if (!$location->isWritable()) {
			throw new \spitfire\exceptions\PrivateException('Cannot write to target', 1805301104);
		}
		
		switch (pathinfo($location->uri(), PATHINFO_EXTENSION)) {
			case 'jpg':
			case 'jpeg':
				imagejpeg($this->img, $this->tmp, $this->compression * 10);
				break;
			case 'png':
			default:
				imagepng($this->img, $this->tmp, $this->compression);
				
				try { \spitfire\io\image\PNGQuant::compress($this->tmp, $this->tmp); }
				catch (\Exception$e) { /*If PNGQuant is not installed, we do nothing*/ }
		}
		
		$location->write(file_get_contents($this->tmp));
		unlink($this->tmp);
		
		return $location;
	}

	public function supports(string $mime): bool {
		
		switch($mime) {
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/png':
			case 'image/gif':
				return true;
			default: 
				return false;
		}
	}

	public function poster(): MediaManipulatorInterface {
		return $this;
	}

	public function dimensions() {
		return Array($this->meta[0], $this->meta[1]);
	}

}
