<?php namespace spitfire\io\session;

use spitfire\App;
use spitfire\core\Environment;

/**
 * The Session class allows your application to write data to a persistent space
 * that automatically expires after a given time. This class allows you to quickly
 * and comfortably select the persistence mechanism you want and continue working.
 * 
 * This class is a <strong>singleton</strong>. I've been working on reducing the
 * amount of single instance objects inside of spitfire, but this class is somewhat
 * special. It represents a single and global resource inside of PHP and therefore
 * will only make the system unstable by allowing several instances.
 */
class Session
{
	
	/**
	 * The session handler is in charge of storing the data to disk once the system
	 * is done reading it.
	 *
	 * @var SessionHandler
	 */
	private $handler;
	
	/**
	 * The Session allows the application to maintain a persistence across HTTP
	 * requests by providing the user with a cookie and maintaining the data on 
	 * the server. Therefore, you can consider all the data you read from the 
	 * session to be safe because it stems from the server.
	 * 
	 * You need to question the fact that the data actually belongs to the same
	 * user, since this may not be guaranteed all the time.
	 * 
	 * @param SessionHandler $handler
	 */
	protected function __construct(SessionHandler$handler = null) {
		$lifetime = 2592000;
		
		if (!$handler) { $handler = new FileSessionHandler(realpath(session_save_path()), $lifetime); }
		
		$this->handler = $handler;
	}
	
	public function getHandler() {
		return $this->handler;
	}
	
	public function setHandler($handler) {
		$this->handler = $handler;
		$this->handler->attach();
		return $this;
	}
		
	public function set($key, $value, $app = null) {
		if ($app === null) {$app = current_context()->app;}
		/* @var $app App */
		$namespace = ($app->getNameSpace())? $app->getNameSpace() : '*';

		if (!self::isStarted()) { $this->start(); }
		$_SESSION[$namespace][$key] = $value;

	}

	public function get($key, $app = null) {
		if ($app === null) {$app = current_context()->app;}
		$namespace = $app && $app->getNameSpace()? $app->getNameSpace() : '*';

		if (!isset($_COOKIE[session_name()])) { return null; }
		if (!self::isStarted()) { $this->start(); }
		return isset($_SESSION[$namespace][$key])? $_SESSION[$namespace][$key] : null;

	}

	public function lock($userdata, App$app = null) {

		$user = Array();
		$user['ip']       = $_SERVER['REMOTE_ADDR'];
		$user['userdata'] = $userdata;
		$user['secure']   = true;

		$this->set('_SF_Auth', $user, $app);

	}
	
	public static function isStarted() {
		return session_status() !== PHP_SESSION_NONE;
	}

	public function isSafe(App$app = null) {

		$user = $this->get('_SF_Auth', $app);
		if ($user) {
			$user['secure'] = $user['secure'] && ($user['ip'] == $_SERVER['REMOTE_ADDR']);

			$this->set('_SF_Auth', $user, $app);
			return $user['secure'];
		}
		else return false;

	}

	public function getUser(App$app = null) {

		$user = $this->get('_SF_Auth', $app);
		return $user? $user['userdata'] : null;

	}

	public function start() {
		if (self::isStarted()) { return; }
		
		$this->handler->attach();
		session_start();
		
		/*
		 * This is a fallback mechanism that allows dynamic extension of sessions,
		 * otherwise a twenty minute session would end after 20 minutes even 
		 * if the user was actively using it.
		 * 
		 * Read on: http://php.net/manual/en/function.session-set-cookie-params.php
		 */
		$lifetime = 2592000;
		setcookie(session_name(), self::sessionId(), time() + $lifetime, '/');
	}

	public function destroy() {
		$this->start();
		return session_destroy();
	}

	/**
	 * This class requires to be managed in "singleton" mode, since there can only
	 * be one session handler for the system.
	 *
	 * @staticvar Session $instance
	 * @return Session
	 */
	public static function getInstance() {
		static $instance = null;

		if ($instance !== null) { return $instance; }

		$handler = Environment::get('session.handler')? : new FileSessionHandler(SESSION_SAVE_PATH);
		return $instance = new Session($handler);
	}
	
	/**
	 * Returns the session ID being used. 
	 * 
	 * Since March 2017 the Spitfire session will validate that the session 
	 * identifier returned is valid. A valid session ID is up to 128 characters
	 * long and contains only alphanumeric characters, dashes and commas.
	 * 
	 * @todo Move to instance
	 * 
	 * @param boolean $allowRegen Allows the function to provide a new SID in case
	 *                            of the session ID not being valid.
	 * 
	 * @return boolean
	 * @throws \Exception
	 */
	public static function sessionId($allowRegen = true){
		
		#Get the session_id the system is using.
		$sid = session_id();
		
		if (!session_id()) {
			$sid = $_COOKIE[session_name()]?? null;
		}
		
		#If the session is valid, we return the ID and we're done.
		if (!$sid || preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $sid)) {
			return $sid;
		}
		
		#Otherwise we'll attempt to repair the broken 
		// if (!$allowRegen || !self::isStarted() || !session_regenerate_id()) {
		// 	throw new \Exception('Session ID ' . ($allowRegen? 'generation' : 'validation') . ' failed');
		// }
		
		return $sid;
	}
}
