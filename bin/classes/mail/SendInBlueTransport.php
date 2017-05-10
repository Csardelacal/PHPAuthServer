<?php namespace mail;

class SendInBlueTransport implements TransportInterface
{
	
	const BASE_URL = 'https://api.sendinblue.com/v2.0';
	
	private $key;
	
	public function __construct($key) {
		$this->key = $key;
	}

	
	public function deliver(\EmailModel $model) {
		
		$post = Array();
		$post['from']    = [\SysSettingModel::getValue('smtp.from'), \SysSettingModel::getValue('smtp.from')];
		$post['to']      = [$model->to => $model->to];
		$post['subject'] = $model->subject;
		$post['text']    = html_entity_decode(strip_tags($model->body));
		$post['html']    = $model->body;
		
		$mailin = new sendinblue\Mailin(self::BASE_URL, $this->key);
		$result = $mailin->send_email($post);
		
		if($result['code'] !== 'success') { throw new \spitfire\exceptions\PrivateException('Could not send'); }
	}

}
