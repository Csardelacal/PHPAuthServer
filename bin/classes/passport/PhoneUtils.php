<?php namespace passport;

class PhoneUtils
{
	
	public static function canonicalize($phone) {
		if (\Strings::startsWith($phone, '00')) { $phone = '+' . substr($phone, 2); }
		return $phone;
	}
}