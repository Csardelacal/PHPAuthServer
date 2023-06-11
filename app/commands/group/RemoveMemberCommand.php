<?php namespace magic3w\phpauth\commands\group;

use GroupModel;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use user\GroupModel as UserGroupModel;
use UserModel;
use UsernameModel;

#[AsCommand(name: 'group:member:remove', description: 'Remove a member from a group')]
class RemoveMemberCommand extends Command
{
	
	const ARG_GROUP = 'group';
	const ARG_USER = 'user';
	
	/**
	 * @todo Add option for app icon
	 * @todo Add option for overriding the appid
	 * @todo Add option for overriding the app secret
	 */
	protected function configure()
	{
		$this->addArgument(
			self::ARG_GROUP,
			InputArgument::REQUIRED,
			'Identifier of the group'
		);
		
		$this->addArgument(
			self::ARG_USER,
			InputArgument::REQUIRED,
			'Identifier of the user'
		);
		
	}
	
	public function execute(InputInterface $input, OutputInterface $output) : int
	{
		
		$group = db()->table(GroupModel::class)->get('groupId', $input->getArgument(self::ARG_GROUP))->first(true);
		$user  = db()->table(UsernameModel::class)->get('name', $input->getArgument(self::ARG_USER))->first(true)->user;
		
		assert($group instanceof GroupModel);
		assert($user instanceof UserModel);
		
		$membership = db()->table(UserGroupModel::class)->get('group', $group)->where('user', $user)->first(true);
		assert($membership instanceof UserGroupModel);
		
		$membership->delete();
		
		return Command::SUCCESS;
	}
}
