<?php namespace magic3w\phpauth\commands;

use AndrewBreksa\RSMQ\Exceptions\QueueNotFoundException;
use AndrewBreksa\RSMQ\RSMQClient;
use GuzzleHttp\Handler\StreamHandler;
use jwt\Base64URL;
use Monolog\Logger;
use Predis\Client;
use Psr\Container\ContainerInterface;
use spitfire\defer\TaskFactory;
use spitfire\defer\WorkerFactory;
use spitfire\provider\Container;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'worker', description: 'Process background tasks')]
class WorkerCommand extends Command
{
	
	private ContainerInterface $container;
	
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		parent::__construct(null);
	}
	
	public function execute(InputInterface $input, OutputInterface $output) : int
	{
		
		/**
		 * @todo Extract this into a proper configuration
		 */
		$client = new RSMQClient(new Client(['host' => 'redis', 'port' => 6379]));
		$queue  = Base64URL::fromString(spitfire()->getCWD());
		
		try {
			$client->getQueueAttributes($queue);
		}
		catch (QueueNotFoundException $e) {
			$client->createQueue($queue, 300, 0, -1);
		}
		
		/**
		 * @todo This should be handled by a service provider instead of being injected
		 * awkwardly in here.
		 */
		$container = new Container($this->container->get(Container::class));
		
		$workerFactory = new WorkerFactory(
			$container,
			$client,
			$queue
		);
		
		$container->set(WorkerFactory::class, $workerFactory);
		$container->set(TaskFactory::class, new TaskFactory($client, $queue));
		
		
		$workerFactory->make()->work();
		
		return Command::SUCCESS;
	}
}
