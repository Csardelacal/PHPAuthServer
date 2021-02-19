<?php namespace mail;

class MailUtils
{
	
	public static function canonicalize($email, $ignorestops = false) {
		list($inbox, $domain) = explode('@', $email);
		list($user) = explode('+', $inbox);
		
		if ($ignorestops) { $user = str_replace('.', '', $user); }
		
		return sprintf('%s@%s', $user, $domain);
	}
	
	public static function parse($email) {
		list($inbox, $domain) = explode('@', $email);
		list($user, $fragment) = explode('+', $inbox);
		
		return [
			'user' => $user,
			'fragment' => $fragment,
			'domain' => $domain
		];
	}
	
	public static function enrich($email, $fragment) {
		$parsed = self::parse($email);
		return sprintf('%s+%s@%s', $parsed['user'], $fragment, $parsed['domain']);
	}
	
}