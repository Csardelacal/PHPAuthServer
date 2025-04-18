<?php namespace magic3w\phpauth\commands\storage;

use access\CodeModel;
use access\TokenModel as AccessTokenModel;
use access\RefreshModel as RefreshTokenModel;
use AndrewBreksa\RSMQ\RSMQClient;
use defer\tasks\IncinerateAccessCodeTask;
use defer\tasks\IncinerateAccessTokenTask;
use defer\tasks\IncinerateLegacyTokenTask;
use defer\tasks\IncinerateRefreshTokenTask;
use defer\tasks\IncinerateSessionTask;
use jwt\Base64URL;
use Predis\Client;
use SessionModel;
use spitfire\defer\TaskFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TokenModel;

#[AsCommand(name: 'storage:prune', description: 'Queues pruning of old data')]
class PruneCommand extends Command
{
	
	/**
	 *
	 * @param int $interval By letting prune know the interval the system prunes at, it can
	 * ensure that database load is evenly staggered across the interval.
	 * 
	 * @param int $retention During the retention period data will not be eliminated, retention
	 * periods allow administrators and moderators to detect ill behavior.
	 */
	protected function configure()
	{
		$this->addArgument(
			'interval',
			InputArgument::OPTIONAL,
			'By letting prune know the interval the system prunes at, it can '. 
			'ensure that database load is evenly staggered across the interval',
			86400
		);
		
		$this->addArgument(
			'retention',
			InputArgument::OPTIONAL,
			'During the retention period data will not be eliminated, retention ' . 
			'periods allow administrators and moderators to detect ill behavior.',
			2592000
		);
	}
	
	public function execute(InputInterface $input, OutputInterface $output)
	{
		$interval = (int)$input->getArgument('interval');
		$retention = (int)$input->getArgument('retention');
		
		$started = time();
		$limit   = $started - $retention;
		
		$client  = new RSMQClient(new Client(['host' => 'redis', 'port' => 6379]));
		$queue   = Base64URL::fromString(spitfire()->getCWD());
		
		$taskFactory = new TaskFactory($client, $queue);
		
		# Start pruning access tokens that were expired but never actively terminated
		db()->table(AccessTokenModel::class)->getAll()->where('expires', '<', $limit)->range(0, 1000)
			->each(fn($e) => $taskFactory->defer(
				$started + rand(0, $interval),
				IncinerateAccessTokenTask::class,
				$e->_id
			));
		
		# Prune refresh tokens that were expired
		db()->table(RefreshTokenModel::class)->getAll()->where('expires', '<', $limit)->range(0, 1000)
			->each(fn($e) => $taskFactory->defer(
				$started + rand(0, $interval),
				IncinerateRefreshTokenTask::class,
				$e->_id
			));
		
		# Prune access codes that were expired
		db()->table(CodeModel::class)->getAll()->where('expires', '<', $limit)->range(0, 5000)
			->each(fn($e) => $taskFactory->defer(
				$started + rand(0, $interval),
				IncinerateAccessCodeTask::class,
				$e->_id
			));
	
		# Prune sessions that were expired
		db()->table(SessionModel::class)->getAll()->where('expires', '<', $limit)->range(0, 5000)
			->each(fn($e) => $taskFactory->defer(
				$started + rand(0, $interval),
				IncinerateSessionTask::class,
				$e->_id
			));
		
		# Prune legacy tokens that were expired
		db()->table(TokenModel::class)->getAll()->where('expires', '<', $limit)->range(0, 5000)
			->each(fn($e) => $taskFactory->defer(
				$started + rand(0, $interval),
				IncinerateLegacyTokenTask::class,
				$e->_id
			));
		
		# Prune emails that were sent and are now expired
		# Since emails do not have side effects to their deletion, we can just remove them
		# TODO: It'd be super cool if this would just delete them 20K instead of pulling them first
		db()->table('email')->getAll()->where('delivered', '<', $limit - 90 * 86400)->range(0, 2000)
			->each(fn($e) => $e->delete());
			
		return Command::SUCCESS;
	}
}
