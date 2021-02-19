<?php

use spitfire\mvc\Director;

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

class CronDirector extends Director
{
	
	public function email() {
		
		console()->success('Initiating cron...')->ln();
		$started   = time();
		$delivered = false;
		
		
		$file = spitfire()->getCWD() . '/bin/usr/.mail.cron.sem';
		$fh = fopen($file, file_exists($file)? 'r' : 'w+');
		
		if (!flock($fh, LOCK_EX)) { 
			console()->error('Could not acquire lock')->ln();
			return 1; 
		}
		
		console()->success('Acquired lock!')->ln();
		
		try {
			$flipflop = new cron\FlipFlop($file);
		} catch (Exception $ex) {
			console()->error('SysV is not enabled, falling back to timed flip-flop')->ln();
			$flipflop = new cron\TimerFlipFlop();
		}
		
		while(($delivered = \email\OutgoingModel::deliver()) || $flipflop->wait()) {
			if ($delivered) {
				console()->success('Email delivered!')->ln();
			}
			
			if (time() > $started + 1200) {
				break;
			}
		}
		
		console()->success('Cron ended, was running for ' . (time() - $started) . ' seconds')->ln();
		
		flock($fh, LOCK_UN);
		
		return 0;
		
	}
	
	/**
	 * Refresh the deliverability information for the email domains. This allows
	 * PHPAS to determine whether it should continue gray-listing domains that it
	 * has been sending email to.
	 * 
	 * @deprecated since version 0.1-dev
	 */
	public function delivery() {
		$domains = db()->table('email\domain')->get('type', mail\spam\domain\StorageInterface::TYPE_HOSTNAME)->where('updated', '<', time() - 7 * 86400)->all();
		
		foreach ($domains as $domain) {
			/*
			 * Count the amount of emails that were clicked in the last 90 days for
			 * this domain.
			 */
			$clicked   = db()->table('email\outgoing')->get('domain', $domain)->where('scheduled', '>', time() - 86400 * 90)->where('clicked', '!=', null)->count();
			
			/*
			 * Count the amount of emails that were sent out but have not yet been
			 * clicked by a user.
			 */
			$unclicked = db()->table('email\outgoing')->get('domain', $domain)->where('scheduled', '>', time() - 86400 * 90)->where('clicked', null)->count();
			$total = $clicked + $unclicked;
			
			if ($total == 0) { continue; }
			
			/*
			 * Record the deliverability to the database.
			 */
			$domain->deliverability = $clicked / $total;
			$domain->store();
			
			console()->info(sprintf('Updated %s(%s/%s)', $domain->host, $clicked, $total))->ln();
		}
	}
	
}