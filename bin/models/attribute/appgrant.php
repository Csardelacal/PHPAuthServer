<?php namespace attribute;

use spitfire\Model;
use spitfire\storage\database\Schema;

/* 
 * The MIT License
 *
 * Copyright 2018 César de la Cal Bretschneider <cesar@magic3w.com>.
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

class AppGrantModel extends Model
{
	
	const GRANT_PENDING = 0x00;
	const GRANT_DENIED  = 0x01;
	const GRANT_READ    = 0x10;
	const GRANT_WRITE   = 0x20;
	const GRANT_RW      = 0x30;
	
	/**
	 * 
	 * @param Schema $schema
	 */
	public function definitions(Schema$schema) {
		$schema->attribute = new \Reference(\AttributeModel::class);
		$schema->app       = new \Reference(\AuthAppModel::class);
		$schema->user      = new \Reference(\UserModel::class);
		$schema->grant     = new \IntegerField(true);
		
		
		$schema->index($schema->attribute, $schema->app, $schema->user)->unique();
		$schema->index($schema->attribute, $schema->app);
		$schema->index($schema->attribute, $schema->user);
	}

}
