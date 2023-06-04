<?php namespace magic3w\phpauth\providers;

use magic3w\phpauth\commands\user\DeleteCommand as UserDeleteCommand;
use magic3w\phpauth\commands\storage\PruneCommand as StoragePruneCommand;
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
		
	}
}
