<?php namespace magic3w\phpauth\providers;

use magic3w\phpauth\commands\app\CreateCommand as AppCreateCommand;
use magic3w\phpauth\commands\app\ShowCommand as AppShowCommand;
use magic3w\phpauth\commands\certificate\CreateCommand as CertificateCreateCommand;
use magic3w\phpauth\commands\email\DispatchCommand as EmailDispatchCommand;
use magic3w\phpauth\commands\email\SummaryCommand as EmailSummaryCommand;
use magic3w\phpauth\commands\user\CreateCommand as UserCreateCommand;
use magic3w\phpauth\commands\user\DeleteCommand as UserDeleteCommand;
use magic3w\phpauth\commands\group\CreateCommand as GroupCreateCommand;
use magic3w\phpauth\commands\group\AddMemberCommand as GroupMemberAddCommand;
use magic3w\phpauth\commands\group\RemoveMemberCommand as GroupMemberRemoveCommand;
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
		$kernel->register($container->get(UserCreateCommand::class));
		$kernel->register($container->get(UserDeleteCommand::class));
		$kernel->register($container->get(AppCreateCommand::class));
		$kernel->register($container->get(AppShowCommand::class));
		$kernel->register($container->get(GroupCreateCommand::class));
		$kernel->register($container->get(GroupMemberAddCommand::class));
		$kernel->register($container->get(GroupMemberRemoveCommand::class));
		$kernel->register($container->get(StoragePruneCommand::class));
		$kernel->register($container->get(WorkerCommand::class));
		$kernel->register($container->get(EmailDispatchCommand::class));
		$kernel->register($container->get(EmailSummaryCommand::class));
		$kernel->register($container->get(StorageStatsCommand::class));
		$kernel->register($container->get(CertificateCreateCommand::class));
		
	}
}
