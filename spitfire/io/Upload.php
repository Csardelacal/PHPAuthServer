<?php namespace spitfire\io;

use spitfire\exceptions\FilePermissionsException;
use spitfire\exceptions\PrivateException;
use spitfire\exceptions\UploadValidationException;
use spitfire\io\Filesize;
use spitfire\storage\objectStorage\DirectoryInterface;
use spitfire\storage\objectStorage\FileInterface;
use Strings;
use function storage;

/**
 * This class merges the file Uploads coming from a client into the POST array,
 * allowing beans and programmers to have easier access to the data coming from
 * the client without trading in any security.
 * 
 * The class should not automatically store any data to avoid the user being able 
 * to inject uploads where unwanted. The class automatically names uploads when
 * storing to avoid collissions, returning the name of the file it stored.
 * 
 * @author César de la Cal <cesar@magic3w.com>
 * @since 0.1
 * @last-revision 2013.07.01
 */
class Upload
{
	/**
	 * Contains the raw metadata that was initially sent with the _FILES array. 
	 * Since this is pretty deeply worked into the way PHP works we should leave it
	 * alone. Changes to the way this array is organized are rare and don't affect
	 * Spitfire based apps hardly.
	 * 
	 * @see http://php.net/manual/en/features.file-upload.post-method.php For the array structure used
	 * @var mixed[]
	 */
	private $meta;
	
	/** 
	 * @var string|null Contains filename if store() was called, null otherwise 
	 */
	private $stored;
	
	/** 
	 * Upload directory path (without trailing slash). This can be changed by 
	 * invoking setUploadDirectory()
	 * 
	 * @var string 
	 */
	private $uploadDir;
	
	public function __construct($meta) {
		$this->meta      = $meta;
		$this->uploadDir = \spitfire\core\Environment::get('uploads.directory')?: 'app://bin/usr/uploads';
	}
	
	public function isOk() {
		
		#First, we check whether the uploaded data was nested.
		if (is_array($this->meta['name'])) {
			throw new PrivateException('Is an upload array');
		}
		
		#If the sent data does not contain a file name the data transmitted was
		#not proper or not properly formatted
		if (empty($this->meta['name'])) {
			throw new PrivateException('Nothing uploaded');
		}
		
		#If the value in error is anything but 0, it will mean that PHP reported
		#an error. Whatever that value is, it's not acceptable.
		if ($this->meta['error'] > 0) {
			throw new PrivateException('Upload error');
		}
		
		return true;
	}
	
	/**
	 * 
	 * @return FileInterface
	 * @throws filePermissionsException
	 */
	public function store() {
		#If the data was already stored (this may happen in certain events where a
		#store function is called several times) return the location of the file.
		if ($this->stored) {
			return $this->stored;
		}
		
		#Check if the uploaded file is ok
		$this->isOk();
		
		#Create the directory to write to
		$dir = storage($this->uploadDir);
		
		#Ensure the directory exists and is writable.
		if (!$dir instanceof DirectoryInterface) { throw new FilePermissionsException('Upload directory is not a directory', 1808200912); }
		if (!$dir->isWritable()) { throw new FilePermissionsException('Upload directory is not writable'); }
		
		#Assemble the different components of the filename. This will be necessary 
		#to tell the system where to write the data to
		$time     = base_convert(time(), 10, 32);
		$rand     = base_convert(rand(), 10, 32);
		$filename = Strings::slug(pathinfo($this->meta['name'], PATHINFO_FILENAME));
		$extension= pathinfo($this->meta['name'], PATHINFO_EXTENSION);
		
		$file = $dir->make($time . '_' . $rand . '_' . $filename . '.' . $extension);
		$file->write(file_get_contents($this->meta['tmp_name']));
		
		#Move the file and return the path.
		return $this->stored = $file;
	}

	/**
	 * This function should be called before storing any uploaded file
	 * 
	 * Validation should be performed in the validation classes and therefore this
	 * method has been marked as deprecated.
	 *
	 * @param string $expect The string corresponting to the type we want to check against
	 * 
	 * @deprecated since version 0.1-dev 20180710
	 * @return self
	 * @throws UploadValidationException
	 */
	public function validate($expect = 'image'){
		switch ($expect){
			case "image":
				$info = getimagesize($this->meta['tmp_name']);
				if ($info === FALSE)
					throw new UploadValidationException('The uploaded file does not appear to be an image', 1703312326);
				if (!in_array($info[2],[IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG]))
					throw new UploadValidationException('The uploaded file does not match any supported file type', 1703312327);
			break;
		}

		return $this;
	}
	
	public function getData() {
		
		if (!is_array($this->meta['name'])) {
			if ($this->meta['size'] == 0) { return null; }
			return $this;
		}
		
		$_return = Array();
		$keys    = array_keys($this->meta['name']);
		
		foreach ($keys as $name) {
			$_return[$name] = $this->$name->getData();
		}
		
		return $_return;
	}
	
	public function setUploadDirectory($to) {
		$this->uploadDir = $to;
		return $this;
	}

	/**
	 * Returns the maximum uploadable file size
	 *
	 * @param Filesize[] $sizes An array of Filesize instances, for use with tests
	 *
	 * @return Filesize
	 */
	static function getMaxUploadSize($sizes = null){
		if (!isset($sizes))
			$sizes = [
				Filesize::parse(ini_get('post_max_size')),
				Filesize::parse(ini_get('upload_max_filesize')),
			];

		// Sort ascending based on bytes
		uasort($sizes, function(Filesize$a, Filesize$b){
			return $a->getSize() > $b->getSize() ? 1 : ($a->getSize() < $b->getSize() ? -1 : 0);
		});

		return $sizes[0];
	}
	
	public function get($attribute) {
		if (!isset($this->meta[$attribute])) { return null; }
		if (is_array($this->meta[$attribute])) { throw new PrivateException('Tried to get attribute of upload array'); }
		
		return $this->meta[$attribute];
	}
	
	public function __get($name) {
		if (isset($this->meta['name'][$name])) {
			return new Upload(Array(
				 'name'     => $this->meta['name'][$name],
				 'type'     => $this->meta['type'][$name],
				 'tmp_name' => $this->meta['tmp_name'][$name],
				 'size'     => $this->meta['size'][$name],
				 'error'    => $this->meta['error'][$name],
			));
		}
	}
	
	public static function init() {
		if (empty($_FILES)) { return Array(); }
		
		$files   = $_FILES;
		
		foreach ($files as &$file) {
			$t = new Upload($file);
			$file = $t->getData();
		}
		
		return $files;
		
	}
}
