<?php

use spitfire\mvc\Director;
use spitfire\io\template\Template;

class EmailSummaryDirector extends Director
{
	/**
	 * Sends an email to the given user-ID with the
	 * summary of the email providers wich were used
	 * to register an account on Commishes in the last 24h.
	 * 
	 * @param  $userID  The userID to send the email to
	 * @return email\Templates;
	 */
	public function send($userID = 1)
	{
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
	}
}
