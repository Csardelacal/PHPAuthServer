<?php

use spitfire\exceptions\FileNotFoundException;
use spitfire\exceptions\PrivateException;
use spitfire\exceptions\PublicException;
use spitfire\io\Image;
use spitfire\storage\objectStorage\FileInterface;

ini_set('memory_limit', '512M');

class ImageController extends Controller
{
	
	private static $thumbSizes = Array( 32, 48, 64, 128, 256 );

	const DEFAULT_APP_ICON = BASEDIR . '/assets/img/app.png';
	
	public function hero() {
		$file = SysSettingModel::getValue('page.logo');
		
		$responseHeaders = $this->response->getHeaders();
		$responseHeaders->set('Content-type', storage()->get($file)->mime());
		$responseHeaders->set('Cache-Control', 'no-transform,public,max-age=3600');
		$responseHeaders->set('Expires', date('r', time() + 3600));
		
		if (ob_get_length() !== 0) {
			throw new Exception('Buffer is not empty... Dumping: ' . __(ob_get_contents()), 1604272248);
		}
		
		return $this->response->setBody(storage()->get($file));
	}
	
	public function app($id, $size = 32) {
		$app  = db()->table('authapp')->get('_id', $id)->fetch();
		
		if (!$app) {
			throw new PublicException('Invalid app id');
		}
		
		try {
			$icon = storage($app->icon)->getPath();
		} 
		catch (Exception $ex) {
			$icon = $app->icon;
		}
		
		if (empty($icon)){
			$icon = self::DEFAULT_APP_ICON;
		}
		
		/*
		 * Define the filename of the target, we store the thumbs for the objects
		 * inside the same directory they get stored to.
		 */
		$file = rtrim(dirname($icon), '\/') . DIRECTORY_SEPARATOR . $size . '_' . basename($icon);
		
		if(!in_array($size, self::$thumbSizes)) {
			throw new PublicException('Invalid size', 1604272250);
		}
		
		if (!file_exists($file)) {
			try {
				$img = new Image($icon);
			}
			catch (PrivateException$e){
				if (strpos($e->getMessage(), "doesn't exist") === false){ throw $e; }

				$img = new Image(self::DEFAULT_APP_ICON);
			}
			$img->fitInto($size, $size);
			$img->store($file);
		}
		
		$responseHeaders = $this->response->getHeaders();
		$responseHeaders->set('Content-type', 'image/png');
		$responseHeaders->set('Cache-Control', 'no-transform,public,max-age=3600');
		$responseHeaders->set('Expires', date('r', time() + 3600));
		
		if (ob_get_length() !== 0) {
			throw new PrivateException('Buffer is not empty... Dumping: ' . __(ob_get_contents()), 1604272248);
		}
		
		return $this->response->setBody(file_get_contents($file));
		
	}
	
	public function user($id, $size = 32) {
		$user  = db()->table('user')->get('_id', $id)->fetch();
		
		if (!$user) {
			throw new PublicException('Invalid user id');
		}
		
		try {
			$icon = $user->picture? storage($user->picture) : storage('app://assets/img/user.png');
		} 
		catch (\Exception $ex) {
			$icon = storage('app://' . $user->picture);
		}
		
		if (!$icon instanceof FileInterface) {
			throw new PublicException('Invalid path', 400);
		}
		
		if(!in_array($size, self::$thumbSizes)) {
			throw new PublicException('Invalid size', 1604272250);
		}
		
		/*
		 * Define the filename of the target, we store the thumbs for the objects
		 * inside the same directory they get stored to.
		 */
		try {
			$file = storage()->dir(spitfire\core\Environment::get('uploads.directory'))->open($size . '_' . $icon->basename() . '.jpg');
		}
		catch (FileNotFoundException$e) {
			$file = storage()->dir(spitfire\core\Environment::get('uploads.directory'))->make($size . '_' . $icon->basename() . '.jpg');
			
			$img  = media()->load($icon)->poster();
			$img->fit($size, $size);
			$img->background(255, 255, 255);
			$img->store($file);
		}
		
		$this->response->getHeaders()->set('Content-type', $file->mime());
		$this->response->getHeaders()->set('Cache-Control', 'no-transform,public,max-age=3600');
		$this->response->getHeaders()->set('Expires', date('r', time() + 3600));
		
		if (ob_get_length() !== 0) {
			throw new Exception('Buffer is not empty... Dumping: ' . __(ob_get_contents()), 1604272248);
		}
		
		return $this->response->setBody($file->read());
		
	}
	
	public function attribute($attribute, $id, $width = 700) {
		$user  = db()->table('user')->get('_id', $id)->fetch();
		$attr  = db()->table('user\attribute')->get('user', $user)->addRestriction('attr__id', $attribute)->fetch();
		
		if (!$user || !$attr) {
			throw new PublicException('Invalid user / attribute id');
		}
		
		try {
			if (!empty($attr->value)) {
				/*@var $file \spitfire\storage\drive\File*/
				$file = storage($attr->value);
			}
			else {
				throw new Exception('No file', 1811031627);
			}
		} 
		catch (Exception $ex) {
			$file = storage()->get('app://' . $attr->value);
		}
		
		
		/*
		 * Define the filename of the target, we store the thumbs for the objects
		 * inside the same directory they get stored to.
		 */
		$dir = storage()->dir(\spitfire\core\Environment::get('uploads.directory'));
		$prvw = $width . '_' . 'auto' . '_' . $file->filename() . '.jpg';
		
		if (!$dir->contains($prvw)) {
			$img = media()->load($file);
			$img->background(255, 255, 255);
			$img->scale($width);
			$img->store($dir->make($prvw));
		}
		
		if (ob_get_length() !== 0) {
			throw new Exception('Buffer is not empty... Dumping: ' . __(ob_get_contents()), 1604272248);
		}
		
		$this->response->getHeaders()->set('Content-type', mime_content_type($prvw));
		$this->response->getHeaders()->set('Cache-Control', 'no-transform,public,max-age=3600');
		$this->response->getHeaders()->set('Expires', date('r', time() + 3600));
		
		return $this->response->setBody($dir->open($prvw));
		
	}
	
}
