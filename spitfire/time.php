<?php

use spitfire\exceptions\PrivateException;
use spitfire\locale\EmptyLocale;

class Time
{
	
	/**
	 * 
	 * @fixme lang() died and is no longer usable.
	 *
	 * @param int      $time
	 * @param int|null $to
	 *
	 * @return string
	 */
	public static function relative($time, $to = null) {
		$to = ($to === null)? time() : $to;
		$diff = $to - $time;
		
		try                          { $lang = _t()->domain('spitfire.time'); } 
		catch (PrivateException $ex) { $lang = new EmptyLocale(); }
		
		if ($diff > 0) {
			if (1 < $ret = (int)($diff / (3600*24*365))) { return $lang->say('%s years ago', $ret); }
			if (1 < $ret = (int)($diff / (3600*24*30)))  { return $lang->say('%s months ago', $ret); }
			if (1 < $ret = (int)($diff / (3600*24*7)))   { return $lang->say('%s weeks ago', $ret); }
			if (1 < $ret = (int)($diff / (3600*24)))     { return $lang->say('%s days ago', $ret); }
			if (1 < $ret = (int)($diff / (3600)))        { return $lang->say('%s hours ago', $ret); }
			if (1 < $ret = (int)($diff / (60)))          { return $lang->say('%s minutes ago', $ret); }
            return $lang->say('%s seconds ago', $diff);
		}
	}
	
}
