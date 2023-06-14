<?php namespace magic3w\phpauth\commands\user;

use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserModel;
use UsernameModel;

#[AsCommand(name: 'user:create', description: 'Create a new user')]
class CreateCommand extends Command
{
	
	const ARG_EMAIL = 'email';
	const ARG_USERNAME = 'username';
	const ARG_PASSWORD = 'password';
	
	/**
	 * @todo Add option for group membership
	 * @todo Add option for user icons
	 */
	protected function configure()
	{
		$this->addArgument(
			self::ARG_USERNAME,
			InputArgument::REQUIRED,
			'The username for the user to be created (must be unique)'
		);
		
		$this->addArgument(
			self::ARG_EMAIL,
			InputArgument::REQUIRED,
			'The email address of the new user (unique)'
		);
		
		/**
		 * @todo Make this step optional for creating user accounts that the person
		 * needs to set the password.
		 */
		$this->addArgument(
			self::ARG_PASSWORD,
			InputArgument::REQUIRED,
			'The password for the new user'
		);
	}
	
	public function execute(InputInterface $input, OutputInterface $output) : int
	{
		/**
		 * @todo Add Respect for validation
		 * @todo Validate password
		 * @todo Validate username
		 */
		if (db()->table(UserModel::class)->get('email', $input->getArgument(self::ARG_EMAIL))->first()) {
			throw new InvalidArgumentException('Email is already in use');
		}
		
		if (db()->table(UsernameModel::class)->get('name', $input->getArgument(self::ARG_USERNAME))->first()) {
			throw new InvalidArgumentException('Username is already in use');
		}
		
		$user = db()->table(UserModel::class)->newRecord();
		assert($user instanceof UserModel);
		
		$user->email = $input->getArgument(self::ARG_EMAIL);
		$user->setPassword($input->getArgument(self::ARG_PASSWORD));
		$user->store();
		
		$username = db()->table(UsernameModel::class)->newRecord();
		assert($username instanceof UsernameModel);
		
		$username->user = $user;
		$username->name = $input->getArgument(self::ARG_USERNAME);
		$username->store();
		
		return Command::SUCCESS;
	}
}
