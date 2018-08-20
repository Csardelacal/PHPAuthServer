<?php

use spitfire\exceptions\PrivateException;

ini_set('memory_limit', '512M');

class ImageController extends Controller
{
	
	private static $thumbSizes = Array( 32, 48, 64, 128, 256 );

	const DEFAULT_APP_ICON = BASEDIR . '/assets/img/app.png';
	
	public function hero() {
		$file = SysSettingModel::getValue('page.logo');
		
		$responseHeaders = $this->response->getHeaders();
		$responseHeaders->set('Content-type', mime_content_type($file));
		$responseHeaders->set('Cache-Control', 'no-transform,public,max-age=3600');
		$responseHeaders->set('Expires', date('r', time() + 3600));
		
		if (ob_get_length() !== 0) {
			throw new Exception('Buffer is not empty... Dumping: ' . __(ob_get_contents()), 1604272248);
		}
		
		return $this->response->setBody(file_get_contents($file));
	}
	
	public function app($id, $size = 32) {
		$app  = db()->table('authapp')->get('_id', $id)->fetch();
		
		if (!$app) {
			throw new spitfire\exceptions\PublicException('Invalid app id');
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
			throw new spitfire\exceptions\PublicException('Invalid size', 1604272250);
		}
		
		if (!file_exists($file)) {
			try {
				$img = new \spitfire\io\Image($icon);
			}
			catch (PrivateException$e){
				if (strpos($e->getMessage(), "doesn't exist") === false){ throw $e; }

				$img = new \spitfire\io\Image(self::DEFAULT_APP_ICON);
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
			throw new spitfire\exceptions\PublicException('Invalid user id');
		}
		
		try {
			$icon = $icon? storage($user->picture)->getPath() : null;
		} 
		catch (Exception $ex) {
			$icon = $user->picture;
		}
		
		/*
		 * Define the filename of the target, we store the thumbs for the objects
		 * inside the same directory they get stored to.
		 */
		$file = rtrim(dirname($icon), '\/') . DIRECTORY_SEPARATOR . $size . '_' . pathinfo($icon, PATHINFO_FILENAME) . '.jpg';
		
		if(!in_array($size, self::$thumbSizes)) {
			throw new spitfire\exceptions\PublicException('Invalid size', 1604272250);
		}
		
		
		if (!file_exists($file) && file_exists($icon)) {
			$img = new \spitfire\io\Image($icon);
			$img->fitInto($size, $size);
			$img->setBackground(255, 255, 255);
			$img->setCompression(9);
			$img->store($file, 'jpg');
		} elseif (!file_exists($icon)) {
			$file = './assets/img/user.png';
		}
		
		$this->response->getHeaders()->set('Content-type', mime($file));
		$this->response->getHeaders()->set('Cache-Control', 'no-transform,public,max-age=3600');
		$this->response->getHeaders()->set('Expires', date('r', time() + 3600));
		
		if (ob_get_length() !== 0) {
			throw new Exception('Buffer is not empty... Dumping: ' . __(ob_get_contents()), 1604272248);
		}
		
		return $this->response->setBody(file_get_contents($file));
		
	}
	
	public function attribute($attribute, $id, $width = 700, $height = null) {
		$user  = db()->table('user')->get('_id', $id)->fetch();
		$attr  = db()->table('user\attribute')->get('user', $user)->addRestriction('attr__id', $attribute)->fetch();
		
		if (!$user || !$attr) {
			throw new spitfire\exceptions\PublicException('Invalid user / attribute id');
		}
		
		try {
			$file = storage($attr->value)->getPath();
		} 
		catch (Exception $ex) {
			$file = $attr->value;
		}
		
		
		/*
		 * Define the filename of the target, we store the thumbs for the objects
		 * inside the same directory they get stored to.
		 */
		$prvw = rtrim(dirname($file), '\/') . DIRECTORY_SEPARATOR . $width . '_' . ($height? : 'auto') . '_' . basename($file);
		
		if ($attr->value && !file_exists($prvw) && file_exists($file)) {
			$img = new \spitfire\io\Image($file);
			$height? $img->fitInto($width, $height) : $img->resize($width);
			$img->setBackground(255, 255, 255);
			$img->setCompression(9);
			$img->store($prvw, 'jpg');
		} elseif (!$attr->value || !file_exists($file)) {
			//Fallback if the attribute was either not set or not an image the system
			//can preview
			$prvw = './assets/img/user.png';
		}
		
		if (ob_get_length() !== 0) {
			throw new Exception('Buffer is not empty... Dumping: ' . __(ob_get_contents()), 1604272248);
		}
		
		$this->response->getHeaders()->set('Content-type', mime_content_type($prvw));
		$this->response->getHeaders()->set('Cache-Control', 'no-transform,public,max-age=3600');
		$this->response->getHeaders()->set('Expires', date('r', time() + 3600));
		
		return $this->response->setBody(file_get_contents($prvw));
		
	}
	
}
