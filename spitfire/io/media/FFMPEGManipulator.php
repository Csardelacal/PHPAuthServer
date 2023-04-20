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

class FFMPEGManipulator implements MediaManipulatorInterface
{
	
	private $src = null;
	
	private $operations = [];
	
	public function blur(): MediaManipulatorInterface {
		$this->operations['blur'] = "boxblur=5:1";
		return $this;
	}

	public function fit($x, $y): MediaManipulatorInterface {
		$w = ((int)($x/2)) * 2;
		$h = ((int)($y/2)) * 2;
		
		$this->operations['scale'] = "scale={$w}:{$h}:force_original_aspect_ratio=increase";
		$this->operations['crop']  = "crop={$w}:{$h}";
		
		return $this;
	}

	public function grayscale(): MediaManipulatorInterface {
		$this->operations['gray'] = "hue=s=0";
		return $this;
	}

	public function load(\spitfire\storage\objectStorage\FileInterface $blob): MediaManipulatorInterface {
		$this->src = $blob;
		$this->operations = [];
		
		return $this;
	}

	public function quality($target = MediaManipulatorInterface::QUALITY_VERYHIGH): MediaManipulatorInterface {
		//Ignore this for now
	}

	public function scale($target, $side = MediaManipulatorInterface::WIDTH): MediaManipulatorInterface {
		if ($side === self::WIDTH) {
			$w = $target;
			$h = -2;
		}
		else {
			$h = $target;
			$w = -2;
		}
		
		$this->operations['scale'] = "scale={$w}:{$h}";
		return $this;
	}

	public function downscale($target, $side = MediaManipulatorInterface::WIDTH): MediaManipulatorInterface {
		if ($side === self::WIDTH) {
			$w = sprintf("'min(%s,floor(iw/2)*2)'", $target);
			$h = -2;
		}
		else {
			$h = sprintf("'min(%s,floor(ih/2)*2)'", $target);
			$w = -2;
		}
		
		$this->operations['scale'] = "scale={$w}:{$h}";
		return $this;
	}

	public function store(\spitfire\storage\objectStorage\FileInterface $location): \spitfire\storage\objectStorage\FileInterface {
		$tmpi = '/tmp/' . rand();
		$tmpo = '/tmp/' . rand() . '.mp4';
				
		file_put_contents($tmpi, $this->src->read());
		exec(sprintf('ffmpeg -i %s -movflags faststart -pix_fmt yuv420p -r ntsc -crf 26 -vf "%s" %s 2>&1', $tmpi, implode(',', $this->operations), $tmpo));
		
		console()->info('Filesize is ' . new \spitfire\io\Filesize(filesize($tmpo)))->ln();

		$location->write(file_get_contents($tmpo));
		
		unlink($tmpi);
		unlink($tmpo);
		
		return $location;
	}

	public function supports(string $mime): bool {
		switch ($mime) {
			case 'image/gif':
			case 'video/mp4':
			case 'video/quicktime':
				return true;
			default:
				return false;
		}
	}

	public function background($r, $g, $b, $alpha = 0): MediaManipulatorInterface {
		return $this;
	}

	public function poster(): MediaManipulatorInterface {
		$tmpi = '/tmp/' . rand();
		$tmpo = '/tmp/' . rand() . '.png';
		
		file_put_contents($tmpi, $this->src->read());
		exec(sprintf('ffmpeg -i %s -ss 00:00:00 -vframes 1 %s 2>&1', $tmpi, $tmpo));
		
		return media()->load(storage()->get('file:/' . $tmpo));
	}

	public function dimensions() {
		$tmpi = '/tmp/' . rand();
		
		file_put_contents($tmpi, $this->src->read());
		$ret = exec(sprintf('ffprobe -v error -show_entries stream=width,height -of csv=p=0:s=x %s', $tmpi));
		
		return explode('x', $ret);
	}

}
