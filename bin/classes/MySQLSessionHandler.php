<?php 

use spitfire\exceptions\FileNotFoundException;
use spitfire\exceptions\PrivateException;
use spitfire\io\session\Session;

class MySQLSessionHandler extends spitfire\io\session\SessionHandler
{
	
	/**
	 *
	 * @var spitfire\storage\database\Table
	 */
	private $table;
	
	private $handle;

	public function __construct($table, $timeout = null) {
		$this->table = $table;
		parent::__construct($timeout);
	}

	public function close() {
		return true;
	}

	public function destroy($id) {
		$record = $this->table->get('_id', $id)->first();
		$record && $record->delete();
		return true;
	}

	public function gc($maxlifetime) {
		if ($this->getTimeout()) { $maxlifetime = $this->getTimeout(); }
		
		$records = $this->table->get('expires', time() - $maxlifetime, '<')->all();
		$records->each(function ($e) { $e->delete(); });

		return true;
	}
	
	public function getHandle() {
		if ($this->handle)         { return $this->handle; }
		if (!Session::sessionId()) { return false; }
		
		
		#Initialize the session itself
		$id   = Session::sessionId(false);
		
		$this->handle = $this->table->get('_id', $id)->first();
		
		if (!$this->handle) {
			$this->handle = $this->table->newRecord();
			$this->handle->_id = $id;
			$this->handle->expires = time() + 90 * 86400;
			$this->handle->store();
		}
		
		return $this->handle;
	}

	public function open($savePath, $sessionName) {
		return true;
	}

	public function read($__garbage) {
		//The system can only read the first 8MB of the session.
		//We do hardcode to improve the performance since PHP will stop at EOF
		$_ret = $this->getHandle()->payload; 
		return (string)$_ret;
	}

	public function write($__garbage, $data) {
		$this->getHandle()->payload = $data;
		$this->getHandle()->expires = time() + 90 * 86400;
		$this->getHandle()->store();
		
		return true;
	}

}
