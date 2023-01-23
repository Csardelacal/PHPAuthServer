<?php

use AndrewBreksa\RSMQ\Exceptions\QueueNotFoundException;
use AndrewBreksa\RSMQ\RSMQClient;
use defer\tasks\IncinerateAccessCodeTask;
use defer\tasks\IncinerateAccessTokenTask;
use spitfire\defer\TaskFactory;
use spitfire\defer\WorkerFactory;
use jwt\Base64URL;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Predis\Client;
use Psr\Log\LoggerInterface;
use spitfire\mvc\Director;
use spitfire\provider\Container;

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
	
	public function email()
	{
		
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
			$flipflop = new cron\TimerFlipFlop($file);
		}
		
		while (($delivered = EmailModel::deliver()) || $flipflop->wait()) {
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
	
	public function defer()
	{
		
		$container = new Container();
		$client    = new RSMQClient(new Client(['host' => 'redis', 'port' => 6379]));
		$queue     = Base64URL::fromString(spitfire()->getCWD());
		
		try {
			$client->getQueueAttributes($queue);
		}
		catch (QueueNotFoundException $e) {
			$client->createQueue($queue, 300, 0, -1);
		}
		
		$workerFactory = new WorkerFactory(
			$container,
			$client,
			$queue
		);
		
		$logger = new Logger('debug');
		$logger->pushHandler(new StreamHandler(STDERR));
		
		$container->set(LoggerInterface::class, $logger);
		$container->set(WorkerFactory::class, $workerFactory);
		$container->set(TaskFactory::class, new TaskFactory($client, $queue));
		
		
		$workerFactory->make()->work();
	}
	
	/**
	 *
	 * @param int $interval By letting prune know the interval the system prunes at, it can
	 * ensure that database load is evenly staggered across the interval.
	 */
	public function prune(int $interval = 86400)
	{
		$started = time();
		$client  = new RSMQClient(new Client(['host' => 'redis', 'port' => 6379]));
		$queue   = Base64URL::fromString(spitfire()->getCWD());
		
		$taskFactory = new TaskFactory($client, $queue);
		
		# Start pruning access tokens that were expired but never actively terminated
		db()->table('access\token')->getAll()->where('expires', '<', $started)->all()
			->each(fn($e) => $taskFactory->defer(
				$started + rand(0, $interval),
				IncinerateAccessTokenTask::class,
				$e->_id
			));
		
		# Prune refresh tokens that were expired
		db()->table('access\refresh')->getAll()->where('expires', '<', $started)->all()
			->each(fn($e) => $taskFactory->defer(
				$started + rand(0, $interval),
				IncinerateRefreshTokenTask::class,
				$e->_id
			));
		
		# Prune access codes that were expired
		db()->table('access\code')->getAll()->where('expires', '<', $started)->all()
			->each(fn($e) => $taskFactory->defer(
				$started + rand(0, $interval),
				IncinerateAccessCodeTask::class,
				$e->_id
			));
	}
}
