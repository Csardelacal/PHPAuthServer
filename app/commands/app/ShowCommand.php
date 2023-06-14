<?php namespace magic3w\phpauth\commands\app;

use AuthAppModel;
use InvalidArgumentException;
use Strings;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:show', description: 'Show the details for an application')]
class ShowCommand extends Command
{
	
	const ARG_ID = 'id';
	
	const OPT_JSON = 'json';
	
	/**
	 */
	protected function configure()
	{	
		$this->addArgument(
			self::ARG_ID,
			null,
			InputOption::VALUE_OPTIONAL,
			'The id of the application, prefix with : for _id instead of appid'
		);
		
		$this->addOption(
			self::OPT_JSON,
			null,
			InputOption::VALUE_NONE
		);
		
	}
	
	public function execute(InputInterface $input, OutputInterface $output) : int
	{
		$id = $input->getArgument(self::ARG_ID);
		$field = 'appID';
		
		if (Strings::startsWith($id, ':')) {
			$field = '_id';
			$id    = substr($id, 1);
		}
		
		
		$application = db()->table(AuthAppModel::class)->get($field, $id)->first(true);
		assert($application instanceof AuthAppModel);
		
		if ($input->getOption(self::OPT_JSON)) {
			echo json_encode([
				'appid' => $application->appID,
				'secret' => $application->appSecret
			]);
		}
		else {
			$table = new Table($output);
			$table->setHeaders(['key', 'value']);
			$table->addRows([
				['appid', $application->appID],
				['secret', $application->appSecret],
			]);
			
			$table->render();
		}
		
		return Command::SUCCESS;
	}
}
