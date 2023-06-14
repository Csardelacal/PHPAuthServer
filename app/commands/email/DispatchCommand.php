<?php namespace magic3w\phpauth\commands\email;

use cron\TimerFlipFlop;
use EmailModel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'email:dispatch', description: 'Send emails')]
class DispatchCommand extends Command
{
	
	public function execute(InputInterface $input, OutputInterface $output) : int
	{
		$output->writeln("Initiating cron...");
		
		$started   = time();
		$delivered = false;
		
		
		$file = spitfire()->getCWD() . '/bin/usr/.mail.cron.sem';
		$fh = fopen($file, file_exists($file)? 'r' : 'w+');
		
		if (!flock($fh, LOCK_EX)) {
			$output->writeln("<error>Could not acquire lock");
			return Command::FAILURE;
		}
		
		$output->writeln('<info>Acquired lock!');
		
		$output->writeln('<error>SysV is not enabled, falling back to timed flip-flop');
		$flipflop = new TimerFlipFlop($file);
		
		while (($delivered = EmailModel::deliver()) || $flipflop->wait()) {
			if ($delivered) {
				$output->writeln('<info>Email delivered!');
			}
			
			if (time() > $started + 1200) {
				break;
			}
		}
		
		$output->writeln('<info>Cron ended, was running for ' . (time() - $started) . ' seconds');
		
		flock($fh, LOCK_UN);
		
		return Command::SUCCESS;
	}
}
