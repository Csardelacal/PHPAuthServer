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

#[AsCommand(name: 'group:member:add', description: 'Add a member to a group')]
class AddMemberCommand extends Command
{
	
	const ARG_GROUP = 'group';
	const ARG_USER = 'user';
	const ARG_ROLE = 'role';
	
	const ROLE_MEMBER = 'member';
	const ROLE_ADMIN = 'admin';
	const ROLE_OWNER = 'owner';
	
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
		
		$this->addArgument(
			self::ARG_ROLE,
			InputArgument::OPTIONAL,
			'Role of the user'
		);
		
	}
	
	public function execute(InputInterface $input, OutputInterface $output) : int
	{
		
		$group = db()->table(GroupModel::class)->get('groupId', $input->getArgument(self::ARG_GROUP))->first(true);
		$user  = db()->table(UsernameModel::class)->get('name', $input->getArgument(self::ARG_USER))->first(true)->user;
		
		assert($group instanceof GroupModel);
		assert($user instanceof UserModel);
		
		if (!in_array($input->getArgument(self::ARG_ROLE), [self::ROLE_ADMIN, self::ROLE_MEMBER, self::ROLE_OWNER])) {
			throw new InvalidArgumentException('Invalid role');
		}
		
		$membership = db()->table(UserGroupModel::class)->newRecord();
		assert($membership instanceof UserGroupModel);
		
		$membership->group = $group;
		$membership->user = $user;
		$membership->role = $input->getArgument(self::ARG_ROLE);
		$membership->store();
		
		return Command::SUCCESS;
	}
}
