<?php namespace spitfire\io\session;

use spitfire\exceptions\FileNotFoundException;
use spitfire\exceptions\PrivateException;
use spitfire\io\session\Session;

class FileSessionHandler extends SessionHandler
{
	
	private $directory;
	
	private $handle;
	
	private $data = false;
	
	public function __construct($directory, $timeout = null)
	{
		$this->directory = $directory;
		parent::__construct($timeout);
	}
	
	public function close() : bool
	{
		flock($this->getHandle(), LOCK_UN);
		fclose($this->getHandle());
		$this->handle = null;
		return true;
	}
	
	public function destroy($id) : bool
	{
		$file = sprintf('%s/sess_%s', $this->directory, $id);
		$this->handle = null;
		file_exists($file) && unlink($file);
		
		return true;
	}
	
	public function gc($maxlifetime) : int|false
	{
		if ($this->getTimeout()) {
			$maxlifetime = $this->getTimeout();
		}
		
		foreach (glob("$this->directory/sess_*") as $file) {
			if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
				unlink($file);
			}
		}
		
		return true;
	}
	
	public function getHandle()
	{
		if ($this->handle) {
			return $this->handle;
		}
		if (!Session::sessionId()) {
			return false;
		}
		
		
		#Initialize the session itself
		$id   = Session::sessionId(false);
		$file = sprintf('%s/sess_%s', $this->directory, $id);
		
		$this->handle = fopen($file, file_exists($file)? 'r+' : 'w+');
		flock($this->handle, LOCK_EX);
		
		return $this->handle;
	}
	
	public function open($savePath, $sessionName) : bool
	{
		if (empty($this->directory)) {
			$this->directory = $savePath;
		}
		
		if (!is_dir($this->directory) && !mkdir($this->directory, 0777, true)) {
			throw new FileNotFoundException($this->directory . 'does not exist and could not be created');
		}
		
		return true;
	}
	
	public function read($__garbage) : string|false
	{
		if (!$this->getHandle()) {
			return false;
		}
		//The system can only read the first 8MB of the session.
		//We do hardcode to improve the performance since PHP will stop at EOF
		fseek($this->getHandle(), 0);
		return $this->data = (string) fread($this->getHandle(), 8 * 1024 * 1024);
	}
	
	public function write($__garbage, $data) : bool
	{
		//If your session contains more than 8MB of data you're probably doing
		//something wrong.
		if (isset($data[8*1024*1024])) {
			throw new PrivateException('Session length overflow', 171228);
		}
		
		if ($data === $this->data) {
			return true;
		}
		
		ftruncate($this->getHandle(), 0);
		fseek($this->getHandle(), 0);
		
		return !!fwrite($this->getHandle(), $data);
	}
}
