<?php namespace defer;

use AndrewBreksa\RSMQ\ExecutorInterface;
use AndrewBreksa\RSMQ\Message;
use AndrewBreksa\RSMQ\QueueWorker;
use AndrewBreksa\RSMQ\RSMQClient;
use AndrewBreksa\RSMQ\WorkerSleepProvider;
use spitfire\provider\Container;
use Throwable;

class WorkerFactory
{
	
	/**
	 * 
	 * @var Container
	 */
	private $container;
	
	/**
	 * 
	 * @var RSMQClient
	 */
	private $client;
	
	/**
	 * 
	 * @var string
	 */
	private $queue;
	
	/**
	 * 
	 * @param RSMQClient $client
	 * @param string $queue
	 */
	public function __construct(Container $container, RSMQClient $client, string $queue)
	{
		$this->client = $client;
		$this->queue = $queue;
		$this->container = $container;
	}
	
	public function make() : QueueWorker
	{

		$executor = new class($this->container) implements ExecutorInterface
		{
			
			/**
			 * 
			 * @var Container
			 */
			private $container;
			
			public function __construct(Container $container)
			{
				$this->container = $container;
			}
			
			public function __invoke(Message $message) : bool 
			{
				$payload = json_decode($message->getMessage());
				
				/*@var $task \spitfire\defer\Task*/
				$task = $this->container->get($payload->task);
				
				/**
				 * If a task is not a task that we can execute, we need to not execute it since it may
				 * cause behavior that we did not anticipate.
				 */
				assert($task instanceof Task);
				
				try {
					$task->body($payload->settings);
					fwrite(STDOUT, 'Task processed successfully - '  . $message->getId());
					fwrite(STDOUT, json_encode($payload, JSON_THROW_ON_ERROR));
				} 
				catch (Throwable $e) 
				{
					fwrite(STDERR, 'Task failed - '  . $message->getId());
					fwrite(STDERR, json_encode($payload, JSON_THROW_ON_ERROR));
					fwrite(STDERR, json_encode((array)$e, JSON_THROW_ON_ERROR));
				}
				
				return true;
			}
		};

		$sleepProvider = new class() implements WorkerSleepProvider
		{	
			public function getSleep() : ?int {
				/**
				 * This allows you to return null to stop the worker, which can be used with something like redis to mark.
				 *
				 * Note that this method is called _before_ we poll for a message, and therefore if it returns null we'll eject
				 * before we process a message.
				 */
				/**
				 * @todo Listen for a signal maybe?
				 */
				return 1;
			}
		};
		
		return new QueueWorker($this->client, $executor, $sleepProvider, $this->queue);
		
	}
	
}
