<?php namespace magic3w\phpas\commands\user;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'user:delete', description: 'Deletes a user account with the given username')]
class DeleteCommand extends Command
{
	protected function configure()
	{
		$this->addArgument('username', InputArgument::REQUIRED, 'The username');
	}
	
	public function execute(InputInterface $input, OutputInterface $output): int
	{
	// public function delete(string $username)
	// {
		$_username = db()->table('username')->get('name', $input->getArgument('username'))->first(true);
		$_user = $_username->user;
		$_user->delete();
		
		$output->writeln('<info>Deleted');
		return Command::SUCCESS;
	}
	
	
}
