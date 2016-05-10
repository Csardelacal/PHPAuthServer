<?php namespace mail;

class MailGunTransport implements TransportInterface
{
	
	private $apiKey;
	private $domain;
	
	public function __construct($domain, $apiKey) {
		$this->domain = $domain;
		$this->apiKey = $apiKey;
	}
	
	public function deliver(\EmailModel $model) {
		
		$post = Array();
		$post['from']    = \SysSettingModel::getValue('smtp.from');
		$post['to']      = $model->to;
		$post['subject'] = $model->subject;
		$post['text']    = html_entity_decode(strip_tags($model->body));
		$post['html']    = $model->body;
		
		#Assemble the curl request
		$ch = curl_init(sprintf('https://api.mailgun.net/v3/%s/messages', $this->domain));
		
		curl_setopt($ch, CURLOPT_USERPWD, 'api:'. $this->apiKey);
		
		#Tell curl we're posting and give it the data
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		
		#We also want to hear back
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$json     = curl_exec($ch);
		$response = $json? json_decode($json) : false;
		
		if (!$response) { throw new \spitfire\exceptions\PublicException('Invalid response from Mailgun'); }
		
		return $response !== false;
	}

}