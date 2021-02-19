<?php

/**
 * @deprecated since version 0.1-dev
 */
class URLController extends BaseController
{
	/**
	 * This is a very simple system to verify whether a message was opened. The 
	 * system briefly checks whether the email actually contains the URL to ensure
	 * that the endpoint does not provide an open relay.
	 * 
	 * @param type $msgid
	 * @param type $encoded
	 */
	public function redirect($msgid, $encoded) {
		
		$msg = db()->table('email\outgoing')->get('_id', $msgid)->first();
		$url = base64_decode(rawurldecode($encoded));
		
		if (strpos($msg->body, $url) === false) {
			throw new PublicException('URL ' . $url . ' was not found in the message');
		}
		
		if ($msg) {
			$msg->clicked = time();
			$msg->store();
		}
		
		/*
		 * If the server is configured with a hook system, we can let the application
		 * know that the email was opened. Generally speaking, this is not very valuable
		 * information, since generally, the application will link to itself.
		 * 
		 * But if your application for example wishes to link to another component,
		 * but whishes to be kept in the loop of the email it sent, you can listen
		 * for events on mail.outgoing.open.appID.*
		 */
		if ($msg && $this->hook) {
			$this->hook->trigger(sprintf('mail.outgoing.open.%s.%s', $msg->app->appID, $msg->_id), [
				'id' => $msg->_id,
				'to' => $msg->to,
				'subject' => $msg->subject
			]);
		}
		
		$this->response->setBody('Redirecting...')->getHeaders()->redirect($url);
	}
	
}