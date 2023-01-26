<?php namespace magic3w\phpauth\sdk;

/**
 *
 * @deprecated
 * @todo Remove
 */
class File
{
	
	private $previewURL;
	private $downloadURL;
	
	public function __construct($previewURL, $downloadURL)
	{
		$this->previewURL = $previewURL;
		$this->downloadURL = $downloadURL;
	}
	
	public function getPreviewURL($w = null, $h = null)
	{
		list($url, $qstring) = explode('?', $this->previewURL, 2);
		return implode('/', array(trim($url, '/'), $w, $h)) . '/?' . $qstring;
	}
	
	public function getDownloadURL()
	{
		return $this->downloadURL;
	}
}
