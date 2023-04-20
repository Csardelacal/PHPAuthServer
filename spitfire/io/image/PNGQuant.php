<?php namespace spitfire\io\image;

use spitfire\exceptions\FileNotFoundException;
use spitfire\exceptions\PrivateException;


class PNGQuant
{
	
	/**
	 * The PNGQuant function will automatically compress a PNG image, taking just the 
	 * path as a parameter. Since it will write the file to the exact same location
	 * that your original file was located, you don't need to do any additional work
	 * to write it.
	 * 
	 * Usually we don't compress originals, to maintain a "as good as possible" copy,
	 * but apply this to thumbs and versions we generated with the rather crummy GD
	 * compression, so you can optionally pass a second parameter to write to a 
	 * different file.
	 * 
	 * @param $img    string The file to read in
	 * @param $target string The file to write to
	 */
	public static function compress($img, $target = null) {
		if (!file_exists($img)) {
			throw new FileNotFoundException("File does not exist: $img");
		}
		
		$descriptors = Array(Array('pipe', 'r'), Array('pipe', 'w'), Array('pipe', 'w'));
		$proc = proc_open('pngquant -', $descriptors, $pipes);
		
		if (is_resource($proc)) {
			fwrite($pipes[0], file_get_contents($img));
			fclose($pipes[0]);
			
			$compressed_png_content = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			
			$error_output = stream_get_contents($pipes[2]);
			fclose($pipes[2]);
			
			$code = proc_close($proc); //Not yet being used, this is just a test
		}

		if (!$compressed_png_content) {
			die($error_output);
			throw new PrivateException('Compressing PNG failed. Is pngquant 1.8+ installed on the server?');
		}

		file_put_contents($target? : $img, $compressed_png_content);
		return $img;
	}
}

