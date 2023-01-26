<?php namespace jwt;

class Base64URL
{
	
	public static function fromBase64(string $b64)
	{
		return trim(str_replace(['/', '+'], ['_', '-'], $b64), '=');
	}
	
	public static function fromString(string $str)
	{
		return self::fromBase64(base64_encode($str));
	}
}
