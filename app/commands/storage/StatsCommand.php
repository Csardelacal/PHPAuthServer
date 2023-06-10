<?php namespace magic3w\phpauth\commands\storage;

use GroupModel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SysSettingModel;
use user\GroupModel as UserGroupModel;
use user\SuspensionModel;

#[AsCommand(name: 'storage:stats', description:'Show stats about the number of records')]
class StatsCommand extends Command
{
	
	
	public function execute(InputInterface $input, OutputInterface $output) : int
	{
		
		$table = new Table($output);
		
		$table->setHeaders(['Type', 'Count']);
		$table->setRows([
			['Usernames', db()->table('username')->getAll()->count()],
			['Groups', db()->table(GroupModel::class)->getAll()->count()],
			['Users', db()->table('user')->getAll()->count()],
			['Group memberships', db()->table(UserGroupModel::class)->getAll()->count()],
			['Suspensions', db()->table(SuspensionModel::class)->getAll()->count()],
			['Settings', db()->table(SysSettingModel::class)->getAll()->count()],
		]);
		
		$table->render();
		
		return Command::SUCCESS;
	}
}
