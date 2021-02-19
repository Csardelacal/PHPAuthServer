<?php

class Base64URL
{
	
	static public function fromBase64(string $b64) 
	{
		return trim(str_replace(['/', '+'], ['_', '-'], $b64), '=');
	}
	
	static public function fromString(string $str) 
	{
		return self::fromBase64(base64_encode($str));
	}
	
}