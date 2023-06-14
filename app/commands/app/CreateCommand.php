<?php namespace magic3w\phpauth\commands\app;

use AuthAppModel;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:create', description: 'Create a new application')]
class CreateCommand extends Command
{
	
	const ARG_NAME = 'name';
	const ARG_URL  = 'url';
	
	const OPT_LOGOUT = 'logout';
	
	/**
	 * @todo Add option for app icon
	 * @todo Add option for overriding the appid
	 * @todo Add option for overriding the app secret
	 */
	protected function configure()
	{
		$this->addArgument(
			self::ARG_NAME,
			InputArgument::REQUIRED,
			'The name of the application'
		);
		
		$this->addArgument(
			self::ARG_URL,
			InputArgument::REQUIRED,
			'The url of the application (Users will be rediected to this URL when authenticated)'
		);
		
		$this->addOption(
			self::OPT_LOGOUT,
			null,
			InputOption::VALUE_OPTIONAL,
			'Optional URL to be invoked when the user terminates a session'
		);
		
	}
	
	public function execute(InputInterface $input, OutputInterface $output) : int
	{
		$application = db()->table(AuthAppModel::class)->newRecord();
		assert($application instanceof AuthAppModel);
		
		do {
			$id = $application->appID = mt_rand();
			$count = db()->table('authapp')->get('appID', $id)->count();
		} while ($count !== 0);
		
		$application->appSecret = preg_replace('/[^a-z\d]/i', '', base64_encode(random_bytes(35)));
		$application->name = $input->getArgument(self::ARG_NAME);
		$application->logout = $input->getOption(self::OPT_LOGOUT);
		$application->system = false;
		$application->drawer = false;
		$application->store();
		
		/**
		 * @todo Output the resulting output
		 * Maybe human readable to the STDERR output and json to STDIN or something
		 */
		return Command::SUCCESS;
	}
}
