<?php namespace magic3w\phpauth\commands\group;

use AuthAppModel;
use GroupModel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use SysSettingModel;

#[AsCommand(name: 'group:create', description: 'Create a new group')]
class CreateCommand extends Command
{
	
	const OPT_ADMIN = 'admin';
	
	const ARG_NAME = 'name';
	const ARG_ID = 'identifier';
	const ARG_DESCRIPTION = 'description';
	
	/**
	 * @todo Add option for app icon
	 * @todo Add option for overriding the appid
	 * @todo Add option for overriding the app secret
	 */
	protected function configure()
	{
		$this->addArgument(
			self::ARG_ID,
			InputArgument::REQUIRED,
			'The id of the application'
		);
		
		$this->addArgument(
			self::ARG_NAME,
			InputArgument::REQUIRED,
			'The name of the application'
		);
		
		$this->addArgument(
			self::ARG_DESCRIPTION,
			InputArgument::OPTIONAL,
			'The description of the application'
		);
		
		$this->addOption(
			self::OPT_ADMIN,
			'a',
			InputOption::VALUE_NONE | InputOption::VALUE_NEGATABLE,
			'Whether the group is the administrative group'
		);
		
	}
	
	public function execute(InputInterface $input, OutputInterface $output) : int
	{
		$group = db()->table(GroupModel::class)->newRecord();
		assert($group instanceof GroupModel);
		
		$group->groupId = $input->getArgument(self::ARG_ID);
		$group->name = $input->getArgument(self::ARG_NAME);
		$group->description = $input->getArgument(self::ARG_DESCRIPTION);
		$group->store();
		
		
		if ($input->getOption(self::OPT_ADMIN)) {
			#Set the group as admin group
			SysSettingModel::setValue('admin.group', $group->_id);
		}
		
		/**
		 * @todo Output the resulting output
		 * Maybe human readable to the STDERR output and json to STDIN or something
		 */
		return Command::SUCCESS;
	}
}
