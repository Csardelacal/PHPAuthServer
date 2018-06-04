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

class TestDirector extends Director
{
	
	/**
	 * 
	 * @param string $arg1
	 * @param string $arg2
	 * @return int
	 */
	public function test($arg1, $arg2) {
		
		$console = console();


		$console->info('Processing...');
		sleep(1);
		$console->rewind()->success("Yeah! We made it")->ln();


		$console->info('Processing again...');
		sleep(1);
		$console->rewind()->info('Continuing to process this very slow task...');
		sleep(1);
		$console->rewind()->error("Oops! DED!")->ln();

		$progress = $console->progress('Downloading...');

		for ($i = 0; $i < 10; $i++) {
			$progress->progress($i/9);
			sleep(1);
		}

		$console->rewind()->success('File downloaded!')->ln();

		$console->info('Checking the file\'s checksum...');
		sleep(1);
		$console->rewind()->error('Checksum missmatched!')->ln();

		$console->success('Somewhat long success message that may get split by the terminal because it is way too long')->ln();
		
		return 1;
	}
	
}