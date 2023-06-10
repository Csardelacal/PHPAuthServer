<?php namespace magic3w\phpauth\providers;

use magic3w\phpauth\commands\email\DispatchCommand as EmailDispatchCommand;
use magic3w\phpauth\commands\email\SummaryCommand as EmailSummaryCommand;
use magic3w\phpauth\commands\user\DeleteCommand as UserDeleteCommand;
use magic3w\phpauth\commands\storage\PruneCommand as StoragePruneCommand;
use magic3w\phpauth\commands\storage\StatsCommand as StorageStatsCommand;
use magic3w\phpauth\commands\WorkerCommand;
use Psr\Container\ContainerInterface;
use spitfire\contracts\core\kernel\ConsoleKernelInterface;
use spitfire\contracts\services\ProviderInterface;

class CommandServiceProvider implements ProviderInterface
{
	public function register(ContainerInterface $container): void
	{
		
	}
	
	public function init(ContainerInterface $container): void
	{
		if (php_sapi_name() !== 'cli') { return; }
		
		/**
		 * @var ConsoleKernelInterface
		 */
		$kernel = $container->get(ConsoleKernelInterface::class);
		$kernel->register(new UserDeleteCommand());
		$kernel->register(new StoragePruneCommand());
		$kernel->register($container->get(WorkerCommand::class));
		$kernel->register($container->get(EmailDispatchCommand::class));
		$kernel->register($container->get(EmailSummaryCommand::class));
		$kernel->register($container->get(StorageStatsCommand::class));
		
	}
}
