<?php namespace attribute\io;

/**
 * The file to JSON class allows the system to properly convert values read from
 * a database to JSON that can be used by third parties
 */
class FileToJson
{
	
	private $file;
	
	public function __construct($model) {
		$this->file = $model;
	}
	
	public function getRaw() {
		if ($this->file === null) { return null; }
		if ($this->file->value === null) { return null; }
		
		return Array(
			'type'     => 'file',
			'preview'  => (string) url('image',    'attribute', $this->file->attr->_id, $this->file->user->_id, ['t' => $this->file->modified])->absolute(),
			'download' => (string) url('download', 'attribute', $this->file->attr->_id, $this->file->user->_id, ['t' => $this->file->modified])->absolute()
		);
	}
	
}
