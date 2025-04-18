<?php

use email\DomainModel;
use magic3w\phpauth\sdk\JWTRS256Parser;
use magic3w\phpauth\sdk\KeySet;
use mail\spam\domain\implementation\SpamDomainModelReader;
use mail\spam\domain\IP;
use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PrivateException;
use spitfire\exceptions\PublicException;
use spitfire\io\XSSToken;
use spitfire\storage\database\pagination\Paginator;
use spitfire\validation\rules\EmptyValidationRule;
use spitfire\validation\ValidationException;

/**
 * The email controller is one of the weirder controllers in PHPAuthServer. This
 * is due to the fact that the system is not intended to deliver email and it only
 * provides this service to ensure that it completely obfuscates the user's email
 * from the apps which use it to log in and authenticate users.
 */
class EmailController extends BaseController
{
	
	/**
	 * Administrators are allowed to see how the current email queue looks and to
	 * check how many emails were sent recently.
	 * 
	 * @throws PublicException
	 */
	public function index() {
		if (!$this->isAdmin) { throw new PublicException('Not authorized', 401); }
		
		if (isset($_GET['history'])) {
			$queue = db()->table('email')->get('scheduled', time(), '<')->addRestriction('delivered', null, 'IS NOT');
			$queue->setOrder('scheduled', 'ASC');
		} else {
			$queue = db()->table('email')->get('scheduled', time(), '<')->addRestriction('delivered', null, 'IS');
			$queue->setOrder('scheduled', 'DESC');
		}
		
		$pag = new Paginator($queue);
		
		$this->view->set('pagination', $pag);
		$this->view->set('records', $pag->records());
	}
	
	/**
	 * 
	 * GET Parameters:
	 * - appId     - Id of the app trying to relay the message
	 * - appSecret - App Secret to authenticate the App
	 * - userId    - Either a valid email or a user id
	 * 
	 * @todo  Introduce email permissions for certain applications
	 * @param int $userid Deprecated, do not use
	 * @throws PublicException
	 * @throws Exception
	 * @throws HTTPMethodException
	 */
	public function send($userid = null) {
		
		//TODO: Add search by username
		
		/**
		 * @todo Loading the keys from the database seems like a process that
		 * could be moved into a service.
		 */
		$keys = db()->table('key')->getAll()
			->group()
				->where('expires', null)
				->where('expires', '>', time())
			->endGroup()
			->all()
			->extract('public');
		
		/**
		 * @todo The JWT parser should be moved into a service. This is a
		 * process that is repeated in multiple places and should be
		 * abstracted away.
		 */
		if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$parser = new JWTRS256Parser(KeySet::fromArray($keys->toArray()));
			sscanf($_SERVER['HTTP_AUTHORIZATION'], 'Bearer %s', $jwt);
			$jwt = $parser->parse($jwt);
		}
		else {
			$jwt = null;
		}
		
		try {
			#Check if the request is post and subject and body are not empty
			if (!$this->request->isPost()) { throw new HTTPMethodException(); }
			
			/*
			 * Retrieve the email / userId from the request. This should either be posted
			 * or getted. 
			 */
			$userid = isset($_GET['to'])? $_GET['to'] : _def($_POST['to'], $userid);

			/*
			 * We check whether we received any data at all via POST for the recipient.
			 * We can obviously not relay any email to any user if we don't know where
			 * to send it to.
			 */
			if (!$userid) { 
				throw new PublicException('This enpoint requires a recipient'); 
			}

			/*
			 * Get the application authorizing the email. Although we do not log this 
			 * right now, it's gonna be invaluable to help determining whether an app
			 * was compromised and is sending garbage.
			 */
			if ($jwt) {
				
				/**
				 * If the token is restricted to be used by another application, it
				 * implies that the application is not okay with it being used by
				 * another application.
				 */
				if ($jwt->claims()->get('aud') !== null) {
					throw new PublicException('Invalid audience', 401);
				}
				
				if ($jwt->claims()->get('uid') !== null) {
					throw new PublicException('Email can only be sent using client credentials', 401);
				}
				
				$app = db()->table('authapp')->get('appID', $jwt->claims()->get('for'))->first(true);
			}
			else { 
				$app = db()->table('authapp')->get('appID', $_GET['appId'])->where('appSecret', $_GET['appSecret'])->first(true);
			}

			if (!$app) { 
				throw new Exception('Could not authenticate the application trying to send the email'); 
			}

			/*
			 * Determine what kind of id you were sent to determine where to send the 
			 * email to.
			 */
			if (filter_var($userid, FILTER_VALIDATE_EMAIL)) {
				$email = $userid;
			}
			elseif(is_numeric($userid)) {
				$user  = db()->table('user')->get('_id', _def($_POST['to'], $userid))->fetch();
				$email = $user->email;
			}
			
			$vsubject = validate()->addRule(new EmptyValidationRule('Subject cannot be empty'));
			$vcontent = validate()->addRule(new EmptyValidationRule('Message body cannot be empty'));
			validate($vsubject->setValue($_POST['subject']), $vcontent->setValue($_POST['body']));
			
			#Create the message and put it into the message queue
			EmailModel::queue($email, $vsubject->getValue(), $vcontent->getValue())->store();
			
			#Everything was okay - that's it. The email will be delivered later
		} 
		catch (ValidationException$e)  {
			$this->view->set('errors', $e->getResult());
		}
		catch (HTTPMethodException$e) {
			//Do nothing, we'll serve it with get
		}
	} 
	
	public function detail(EmailModel$msg) {
		
		if (!$this->isAdmin) {
			throw new PublicException('Unauthorized', 403);
		}
		
		$this->view->set('msg', $msg);
	}
	
	public function domain() {
		
		if (!$this->isAdmin) {
			throw new PublicException('Unauthorized', 403);
		}
		
		$q = db()->table('email\domain')->getAll();
		
		$p = new Paginator($q);
		
		$this->view->set('xsrf', new XSSToken());
		$this->view->set('records', $p->records());
		$this->view->set('pages', $p);
	}
	
	/**
	 * 
	 * @validate >> POST#hostname(required string) AND POST#reason (required string)
	 * @validate >> POST#list(required string in[white, black]) AND POST#type(required string in[IP, domain])
	 * @param DomainModel $domain
	 */
	public function rule(DomainModel$domain = null) {
		
		if ($domain === null) {
			$domain = db()->table('email\domain')->newRecord();
		}
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException(); }
			if (!$this->validation->isEmpty()) { throw new ValidationException('', 0, $this->validation->toArray()); }
			
			if ($_POST['type'] === 'IP') {
				$pieces = explode('/', $_POST['hostname']);
				$ip   = array_shift($pieces);
				$cidr = array_shift($pieces)? : 0;
				
				if ($cidr % 4) { throw new PrivateException('CIDR must be a value divisible by 4', 1806211156); }
				
				$t = new IP($ip, $cidr);
				$hostname = $t->getBase64();
				$type     = SpamDomainModelReader::TYPE_IP;
			}
			else {
				$hostname = $_POST['hostname'];
				$type     = SpamDomainModelReader::TYPE_HOSTNAME;
			}
			
			if ($_POST['list'] === 'black') {
				$list = SpamDomainModelReader::LIST_BLACKLIST;
			}
			else {
				$list = SpamDomainModelReader::LIST_WHITELIST;
			}
			
			$domain->type = $type;
			$domain->host = $hostname;
			$domain->list = $list;
			$domain->reason = $_POST['reason'];
			$domain->store();
			
			return $this->response->setBody('Redirecting...')->getHeaders()->redirect(url('email', 'rule', $domain->_id));
		} 
		catch (HTTPMethodException $ex) {
			//Do nothing, just show the form
		}
		catch (ValidationException$e) {
			$this->view->set('messages', $e->getResult());
		}
		
		$this->view->set('domain', $domain);
	}
	
	/**
	 * 
	 * @validate GET#xsrf(required string)
	 * @param DomainModel $d
	 */
	public function dropRule(DomainModel$d) {
		
		$xsrf = new XSSToken();
		
		if ($xsrf->verify($_GET['xsrf'])) {
			$d->delete();
		}
		
		
	}
}
