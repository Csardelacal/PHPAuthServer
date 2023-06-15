<?php namespace magic3w\phpauth\commands\certificate;


use KeyModel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'certificate:create', description: 'Generate a new certificate')]
class CreateCommand extends Command
{
	
	
	protected function configure()
	{
		
	}
	
	public function execute(InputInterface $input, OutputInterface $output) : int
	{
		[$private, $public] = KeyModel::generate();
		
		$existing = db()->table('key')->get('expires', null)->first();
		
		if ($existing) {
			$existing->expires = time() + 14 * 86400;
			$existing->store();
		}
		
		$key = db()->table('key')->newRecord();
		$key->public = $public;
		$key->private = $private;
		$key->store();
		
		return Command::SUCCESS;
	}
}
