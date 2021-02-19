<?php

use spitfire\Model;
use spitfire\storage\database\Schema;

/* 
 * The MIT License
 *
 * Copyright 2020 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class DeviceModel extends Model
{
	
	const TYPE_PHONE   = 'smartphone';
	const TYPE_TABLET  = 'tablet';
	const TYPE_LAPTOP  = 'laptop';
	const TYPE_DESKTOP = 'desktop';
	const TYPE_ROBOT   = 'robot';
	
	/**
	 * 
	 * @param Schema $schema
	 * @return Schema
	 */
	public function definitions(Schema $schema) {
		$schema->ua       = new StringField(255);
		$schema->platform = new StringField(128);
		$schema->touch    = new BooleanField();
		$schema->wide     = new BooleanField();
		$schema->js       = new BooleanField();
		$schema->created  = new IntegerField(true);
	}
	
	public static function makeFromRequest() {
		
		# POST data is often encoded as text, booleans suffer the effects of this
		$device = array_map(function ($e) { return $e === true || $e === 'true'; }, $_POST['device']);
		
		$record = db()->table('device')->newRecord();
		$record->ua       = $_SERVER['HTTP_USER_AGENT'];
		$record->platform = $_POST['device']['platform']?? 'Unknown';
		$record->js       = $device['js']?? false;
		$record->touch    = $device['touch']?? false;
		$record->wide     = $device['wide']?? false;
		$record->created  = time();
		$record->store();
		
		return $record;
	}
	
	public function category() {
		if (!$this->js) { return self::TYPE_ROBOT; }
		if ($this->touch && $this->wide) { return self::TYPE_TABLET; }
		if ($this->touch && !$this->wide) { return self::TYPE_PHONE; }
		if (!$this->touch && $this->wide) { return self::TYPE_DESKTOP; }
		if (!$this->touch && !$this->wide) { return self::TYPE_DESKTOP; }
		
		return self::TYPE_ROBOT;
	}

}
