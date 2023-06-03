<?php namespace magic3w\phpas\providers;

use magic3w\phpas\commands\user\DeleteCommand as UserDeleteCommand;
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
		
	}
}
