<?php namespace defer\incinerate;

use spitfire\defer\Result;
use spitfire\defer\Task;

/* 
 * The MIT License
 *
 * Copyright 2021 César de la Cal Bretschneider <cesar@magic3w.com>.
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

/**
 * This task incinerates icons that were replaced by the system for newer ones.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class IconTask implements Task
{
	
	/**
	 * We need access to a Task Factory in case the task is being executed ahead 
	 * of the icon's expiration.
	 *
	 * @var \spitfire\defer\TaskFactory
	 */
	private $defer;
	
	public function __construct(TaskFactory $defer)
	{
		$this->defer = $defer;
	}
	
	/**
	 * Incinerates the icon, removing the database record and the file on the 
	 * drive.
	 * 
	 * @return Result
	 */
	public function body($settings): Result 
	{
		$icon = db()->table(\IconModel::class)->get('_id', $settings)->first(true);
		
		/*
		 * If the icon was restored from the bin (for no particular reason) the system
		 * should not delete the record.
		 */
		if ($icon->expires === null) 
		{
			return new Result('Icon does not expire.');
		}
		
		/*
		 * If the expiration date is in the future, we re-schedule the attempt to
		 * incinerate it to ensure that the data is no longer being used.
		 */
		if ($icon->expires > time()) 
		{
			$this->defer->defer($icon->expires, __CLASS__, $settings);
			return new Result('Icon was not yet expired. Retrying later.');
		}
		
		/*
		 * Delete the file the record points to.
		 */
		storage()->retrieve($icon->file)->delete();
		$icon->delete();
		
		return new Result('Icon has been incinerated');
	}

}
