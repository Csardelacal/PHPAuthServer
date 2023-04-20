<?php namespace spitfire\io\media;

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

class ImagickManipulator implements MediaManipulatorInterface
{
	
	private $tmp = null;
	
	/**
	 *
	 * @var \Imagick
	 */
	private $img;
	
	public function blur(): MediaManipulatorInterface {
		$this->img->adaptiveblurimage(0, 10);
		return $this;
	}

	public function fit($x, $y): MediaManipulatorInterface {
		$this->img->cropthumbnailimage($x, $y);
		return $this;
	}

	public function grayscale(): MediaManipulatorInterface {
		$this->img->modulateImage(100,0,100);
		return $this;
	}

	public function load(\spitfire\storage\objectStorage\FileInterface $blob): MediaManipulatorInterface {
		if ($this->tmp) {
			unlink($this->tmp);
		}
		
		$this->tmp = '/tmp/' . rand();
		file_put_contents($this->tmp, $blob->read());
		
		if (class_exists("Imagick")) {
			set_time_limit(480);
			$img = new Imagick();
			$img->readimage($this->tmp . '[0]');
			$img->setImageIndex(0);
			return $this->img = $img;
		}
		else {
			throw new PrivateException('Imagick was enabled, but not installed', 1805301039);
		}
	}

	public function quality($target = MediaManipulatorInterface::QUALITY_VERYHIGH): MediaManipulatorInterface {
		//TODO Implement
	}

	public function scale($target, $side = MediaManipulatorInterface::WIDTH): MediaManipulatorInterface {
		if ($side === MediaManipulatorInterface::WIDTH) { $this->img->scaleImage($target, 0); }
		else                                            { $this->img->scaleimage(0, $target); }
		return $this;
	}

	public function store(\spitfire\storage\objectStorage\FileInterface $location): \spitfire\storage\objectStorage\FileInterface {
		$this->img->writeimage($this->tmp);
		$location->write(file_get_contents($this->tmp));
		
		unlink($this->tmp);
		$this->tmp = null;
		
		return $location;
	}

	public function supports(string $mime): bool {
		switch($mime) {
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
			case 'image/psd':
			case 'image/vnd.adobe.photoshop':
				return true;
			default:
				return false;
		}
	}

	public function background($r, $g, $b, $alpha = 0): MediaManipulatorInterface {
		$this->img->setimagebackgroundcolor(new \ImagickPixel(sprintf('rgba(%d, %d, %d, %f)'), $r, $g, $g, $alpha));
		$this->img->mergeimagelayers(Imagick::LAYERMETHOD_FLATTEN);
		return $this;
	}

	public function poster(): MediaManipulatorInterface {
		return $this;
	}
	
	public function dimensions() {
		return $this->img->getimagegeometry();
	}

}
