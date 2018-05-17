<?php namespace mail;

class PostalTransport implements TransportInterface
{
	
	private $apiKey;
	private $domain;
	
	public function __construct($domain, $apiKey) {
		$this->domain = $domain;
		$this->apiKey = $apiKey;
	}
	
	public function deliver(\EmailModel $model) {
		
		$post = Array();
		$post['from']       = \SysSettingModel::getValue('smtp.from');
		$post['to']         = [$model->to];
		$post['subject']    = $model->subject;
		$post['plain_body'] = html_entity_decode(strip_tags($model->body));
		$post['html_body']  = $model->body;
		
		#Assemble the curl request
		$ch = curl_init(sprintf('https://%s/api/v1/send/message', $this->domain));
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Server-API-Key:' . $this->apiKey, 'Content-type: application/json']);
		
		#Tell curl we're posting and give it the data
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
		
		#We also want to hear back
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$json     = curl_exec($ch);
		$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$response = $json? json_decode($json) : false;
		
		if ($status !== 200) { throw new \spitfire\exceptions\PublicException('Postal failure. Status: ' . $status, 500); }
		if (!$response)      { throw new \spitfire\exceptions\PublicException('Invalid response from Postal'); }
		
		return $response !== false;
	}

}