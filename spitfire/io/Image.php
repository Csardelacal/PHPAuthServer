<?php namespace spitfire\io;

use spitfire\exceptions\PrivateException;

class Image
{
	private $img;
	private $meta;
	private $compression = 0;
	
	public function __construct($file) {
		$this->img = $this->readFile($file);
	}
	
	public function readFile($file) {
		if (!file_exists($file)) {
			throw new PrivateException("Image file doesn't exist");
		}

		$this->meta = getimagesize($file);

		if (!function_exists('imagecreatefrompng')) {
			throw new PrivateException("GD is not installed.");
		}
		
		switch($this->meta[2]) {
			case IMAGETYPE_PNG: 
				$img = imagecreatefrompng($file);
				imagealphablending($img, false);
				imagesavealpha($img, true);
				return $img;
			case IMAGETYPE_JPEG: 
				return imagecreatefromjpeg($file);
			case IMAGETYPE_GIF: 
				return imagecreatefromgif($file);
			// Disallowing PSD here too for consistency
			/* case IMAGETYPE_PSD:
				if (class_exists("Imagick")) {
					set_time_limit(480);
					$img = new Imagick();
					$img->readimage($file . '[0]');
					$img->setImageIndex(0);
					return $img;
				}
				throw new PrivateException('Spitfire requires Imagemagick to handle PSD files'); */
			default:
				throw new PrivateException('Unsupported image type: ' . $this->meta[2]);
		}
		
	}
	
	public function setBackground($r, $g, $b, $alpha = 0) {
		
		$img = imagecreatetruecolor($this->meta[0], $this->meta[1]);
		imagecolortransparent($img , imagecolorallocatealpha($img , 255, 255, 255, 127));
		imagealphablending($img, true);
		imagesavealpha($img, true);
		
		$bgcolor = imagecolorallocatealpha($img, $r, $g, $b, $alpha);
		imagefilledrectangle($img, 0, 0, $this->meta[0], $this->meta[1], $bgcolor);
		
		imagecopy($img, $this->img, 0, 0, 0, 0, $this->meta[0], $this->meta[1]);
		$this->img = $img;
	}
	
	public function crop($x1, $y1, $x2, $y2) {
		
		$width  = $x2 - $x1;
		$height = $y2 - $y1;
		
		$img = imagecreate($width, $height);
		imagecopy($img, $this->img, 0, 0, $x1, $y1, $width, $height);
		
		$this->img = img;
		$this->meta[0] = $x2 - $x1;
		$this->meta[1] = $y2 - $y1;
		
		return $this;
	}
	
	public function fitInto ($width, $height) {
		
		if ($this->img instanceof Imagick) {
			$this->img->cropthumbnailimage($width, $height);
			return $this;
		}
		
		$wider = ($this->meta[0] / $width) > ($this->meta[1] / $height);
		
		if ($wider) {
			$ratio    = $this->meta[1] / $height;
			$offset_x = ($this->meta[0] - $width * $ratio) / 2;
			$offset_y = 0;
		}
		else {
			$ratio    = $this->meta[0] / $width;
			$offset_y = ($this->meta[1] - $height * $ratio) / 2;
			$offset_x = 0;
		}

		if ($offset_x == 0 && $offset_y == 0){
			$width = min($this->meta[0], $width);
			$height = min($this->meta[1], $height);
		}
		
		$img = imagecreatetruecolor($width, $height);
		imagecolortransparent($img , imagecolorallocatealpha($img , 255, 255, 255, 127));
		imagealphablending($img, false);
		imagesavealpha($img, true);
		imagecopyresampled($img, $this->img, 0, 0, $offset_x, $offset_y, $width, $height, $this->meta[0]-2*$offset_x, $this->meta[1]-2*$offset_y);
		$this->img = $img;
		
		$this->meta[0] = $width;
		$this->meta[1] = $height;
		
		return $this;
	}
	
	public function resize ($width, $height = null) {
		
		if ($width === null) {
			$width = $this->meta[0] * $height / $this->meta[1];
		}
		
		if ($height === null) {
			$height = $this->meta[1] * $width / $this->meta[0];
		}
		
		$img = imagecreatetruecolor($width, $height);
		imagecolortransparent($img , imagecolorallocatealpha($img , 0, 0, 0, 127));
		imagealphablending($img, false);
		imagesavealpha($img, true);
		imagecopyresampled($img, $this->img, 0, 0, 0, 0, $width, $height, $this->meta[0], $this->meta[1]);
		$this->img = $img;
		
		$this->meta[0] = $width;
		$this->meta[1] = $height;
		
		return $this;
		
		
	}
	
	public function grayScale() {
		if ($this->img instanceof \Imagick) {
			$this->img->modulateImage(100,0,100);
			return;
		}
		
		imagefilter($this->img, IMG_FILTER_GRAYSCALE);
		return;
	}
	
	public function setCompression($compression) {
		$this->compression = $compression;
	}
	
	public function getCompression() {
		return $this->compression;
	}
	
	public function store ($file, $filetype = 'png') {
		if (file_exists($file)) {
			unlink ($file);
		}
		
		if (!is_writable(dirname($file))) {
			throw new \spitfire\exceptions\PrivateException('Invalid directory');
		}
		
		if ($this->img instanceof Imagick) {
			$this->img->setImageFormat($filetype);
			$this->img->writeimage(getcwd() . '/' . $file);
		} 
		else {
			switch ($filetype) {
				case 'png':
					imagepng($this->img, $file, $this->compression);
					break;
				case 'jpg':
					imagejpeg($this->img, $file, $this->compression * 10);
			}
		}
		
		return $file;
	}
}
