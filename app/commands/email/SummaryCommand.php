<?php namespace magic3w\phpauth\commands\email;

use EmailModel;
use spitfire\io\template\Template;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'email:summary', description: 'Send email with signups')]
class SummaryCommand extends Command
{
	
	protected function configure()
	{
		$this->addArgument('userid');
	}
	
	/**
	 * Sends an email to the given user-ID with the
	 * summary of the email providers wich were used
	 * to register an account on Commishes in the last 24h.
	 * 
	 * @param  $userID  The userID to send the email to
	 * @return email\Templates;
	 */
	public function execute(InputInterface $input, OutputInterface $output) : int
	{
		$userID = (int)$input->getArgument('userid');
		
		$users  = db()->table('user')->get('created', time() - 86400, '>=')->all();
		$emails = [];

		foreach ($users as $user) {
			$email    = $user->email;
			$provider = explode('@', $email);

			array_push($emails, $provider[1]);
		}

		$view = new Template(spitfire()->getCWD() . '/bin/templates/_email/emailSummary.php');
		
		$data = [
			'emails' => array_count_values($emails),
		];
		
		EmailModel::queue(
			db()->table('user')->get('_id', $userID)->first(true)->email,
			'Your daily email-provider summary!',
			$view->render($data)
		);
		
		return Command::SUCCESS;
	}
}
