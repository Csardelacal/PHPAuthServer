<?php namespace spitfire\io;

use spitfire\io\renderers\RenderableFieldHidden;
use spitfire\io\session\Session;
use spitfire\exceptions\PublicException;

class XSSToken implements RenderableFieldHidden
{
	
	private $timeout;
	
	public function __construct($timeout = 600) {
		$this->timeout = $timeout;
	}
	
	public function getEnforcedFieldRenderer() {
		return null;
	}

	public function getPostId() {
		return '_XSS_';
	}

	public function getValue() {
		$session = Session::getInstance();
		
		if (false == $xss_token = $session->get('_XSS_')) {
			$xss_token = str_replace(['/', '='], [''], base64_encode(function_exists('random_bytes')? random_bytes(50) : rand()));
			$session->set('_XSS_', $xss_token);
		}
		
		$expires = time() + $this->timeout;
		$salt    = str_replace(['/', '='], [''], base64_encode(random_bytes(20)));
		$hash    = hash('sha512', implode('.', [$expires, $salt, $xss_token]));
		
		return implode(':', [$expires, $salt, $hash]);
	}
	
	public function verify($token) {
		$session = Session::getInstance();
		
		if (false == $xss_token = $session->get('_XSS_')) {
			$xss_token = base64_encode(function_exists('random_bytes')? random_bytes(50) : rand());
			$session->set('_XSS_', $xss_token);
		}
		
		$pieces = explode(':', $token);
		
		if (count($pieces) !== 3) {
			throw new PublicException('Malformed XSRF Token', 401);
		}
		
		list($expires, $salt, $hash) = $pieces;
		
		if ($expires < time()) {
			return false;
		}
		
		$check = hash('sha512', implode('.', [$expires, $salt, $xss_token]));
		
		return $hash === $check;
	}

	public function getVisibility() {
		return renderers\RenderableField::VISIBILITY_FORM;
	}

	public function getCaption() {
		return null;
	}
	
	public function __toString() {
		return $this->getValue();
	}

}