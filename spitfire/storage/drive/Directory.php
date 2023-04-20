<?php namespace spitfire\storage\drive;

use spitfire\collection\CollectionInterface;
use spitfire\exceptions\FileNotFoundException;
use spitfire\exceptions\FilePermissionsException;
use spitfire\storage\objectStorage\DirectoryInterface;
use spitfire\storage\objectStorage\FileInterface;
use spitfire\storage\objectStorage\NodeInterface;
use function collect;

class Directory implements DirectoryInterface
{
	
	private $path;
	
	private $parent;
	
	public function __construct(NodeInterface$parent, $name) {
		$this->parent = $parent;
		$this->path = $name? rtrim($name, '\/') : '';
	}
	
	public function isWritable() : bool {
		return is_writable($this->getPath());
	}
	
	public function make($name) : FileInterface {
		if (file_exists($this->getPath() . '/' . $name)) {
			throw new FilePermissionsException('File ' . $name . ' already exists', 1805301554);
		}
		
		return new File($this, $name);
	}

	public function all(): CollectionInterface {
		$contents = scandir($this->getPath());
		
		return collect($contents)->each(function ($e) {
			if (is_dir($this->getPath() . $e)) { return new Directory($this, $e); }
			else                                { return new File($this, $e); }
		});
	}

	public function uri() : string {
		return $this->up()->uri() . $this->path . '/';
	}
	
	public function getPath() {
		return rtrim($this->up()->getPath() . $this->path) . DIRECTORY_SEPARATOR;
	}

	public function up(): NodeInterface {
		return $this->parent;
	}

	public function mkdir($name): NodeInterface {
		
		#We run a recursive mkdir to create the directories needed to get to the 
		#path. If this feils, we'll throw an exception.
		if (!mkdir($this->getPath() . $name, 0755, true)) {
			throw new FilePermissionsException('Could not create ' . $this->path . ' - Permission denied', 1807231752);
		}
		
		return $this->open($name);
	}

	public function open($name): NodeInterface {
		$path = $this->getPath() . $name;
		
		if (is_dir($path)) { 
			return new Directory($this, $name); 
		}
		elseif(file_exists($path)) { 
			return new File($this, $name); 
		}
		
		throw new FileNotFoundException($path . ' was not found', 1805301553);
	}

	public function contains($name): int {
		
		$path = $this->getPath() . $name;
		
		if (is_dir($path)) { 
			return DirectoryInterface::CONTAINS_DIR;
		}
		elseif(file_exists($path)) { 
			return DirectoryInterface::CONTAINS_FILE;
		}
		else {
			return DirectoryInterface::CONTAINS_NONX;
		}
	}

	public function delete(): bool {
		return rmdir($this->getPath());
	}

}
