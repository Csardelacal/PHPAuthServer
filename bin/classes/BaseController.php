<?php

use AndrewBreksa\RSMQ\RSMQClient;
use spitfire\defer\TaskFactory;
use jwt\Base64URL;
use Predis\Client;
use spitfire\exceptions\PrivateException;
use spitfire\io\session\Session;
use spitfire\exceptions\PublicException;

abstract class BaseController extends Controller
{
	/** @var UserModel|null */
	protected $user = null;
	protected $token = null;
	protected $isAdmin = false;
	
	protected ?SessionModel $session = null;
	
	/**
	 *
	 * @var \signature\Helper
	 */
	protected $signature;
	protected $authapp;
	
	protected TaskFactory $defer;
	
	/**
	 *
	 * @var hook\Hook
	 */
	protected $hook;
	
	public function _onload()
	{
		
		$this->defer = new TaskFactory(
			new RSMQClient(new Client(['host' => 'redis', 'port' => 6379])),
			Base64URL::fromString(spitfire()->getCWD())
		);
		
		#Get the user session, if no session is given - we skip all of the processing
		#The user could also check the token
		$s = Session::getInstance();
		$t = isset($_GET['token'])? db()->table('token')->get('token', $_GET['token'])->fetch() : null;
		
		try {
			#Check if the user is an administrator
			$admingroupid = SysSettingModel::getValue('admin.group');
		}
		catch (PrivateException$e) {
			$admingroupid = null;
		}
		
		/**
		 *
		 * @todo The session should be correctly created during login or registration,
		 * there is no good reason for this sanity check ocurring here.
		 *
		 * This section is basically repeat code from the log-in and registration of the
		 * user and should be deprecated and subsequently removed.
		 */
		$this->session = db()->table('session')
			->get('_id', SessionModel::TOKEN_PREFIX . Session::sessionId())
			->first();
		
		if ($this->session === null) {
			$this->session = db()->table('session')->newRecord();
			$this->session->_id = SessionModel::TOKEN_PREFIX . Session::sessionId();
			
			$this->session->expires = time() + 365 * 86400;
			
			/*
			* Retrieve the IP information from the client. This should allow the
			* application to provide the user with data where they connected from.
			*
			* @todo While Cloudflare is very convenient. It's definitely not a generic
			* protocol and produces vendor lock-in. This should be replaced with an
			* interface that allows using a different vendor for location detection.
			*/
			$this->session->ip      = bin2hex(inet_pton($_SERVER["HTTP_X_FORWARDED_FOR"]?? $_SERVER["REMOTE_IP"]));
			$this->session->country = $_SERVER["HTTP_CF_IPCOUNTRY"];
			$this->session->city    = substr($_SERVER["HTTP_CF_IPCITY"], 0, 20);
			$this->session->store();
		}
		
		if ($this->session->expires < time()) {
			$this->session->user = null;
			$this->session->store();
		}
		
		if ($this->session->expires < time() + 90 * 86400) {
			$this->session->expires = time() + 365 * 86400;
			$this->session->store();
		}
		
		$u = $this->session? $this->session->user : null;
		
		if ($u || $t) {
			#Export the user to the controllers that may need it.
			$user = $u?: $t->user;
			$this->user  = $user;
			$this->token = $t;
			
			$isAdmin = !!db()->table(user\GroupModel::class)
				->get('group__id', $admingroupid)
				->where('user', $user)
				->fetch();
		}
		
		$this->signature = new \signature\Helper(db());
		
		if (isset($_GET['signature']) && is_string($_GET['signature'])) {
			list($signature, $src, $target) = $this->signature->verify();
			
			if ($target) {
				throw new PublicException('_GET[signature] must not have remotes', 401);
			}
			
			$this->authapp = $src;
		}
		
		/*
		 * Webhook initialization
		 */
		if (null !== $hookapp = SysSettingModel::getValue('cptn.h00k')) {
			$hook = db()->table('authapp')->get('_id', $hookapp)->first();
			$sig = $this->signature->make($hook->appID, $hook->appSecret, $hook->appID);
			$this->hook = new hook\Hook($hook->url, $sig);
		}
		
		$this->isAdmin = $isAdmin?? false;
		$this->view->set('authUser', $this->user);
		$this->view->set('authApp', $this->app);
		$this->view->set('userIsAdmin', $isAdmin ?? false);
		$this->view->set('administrativeGroup', $admingroupid);
	}
}
