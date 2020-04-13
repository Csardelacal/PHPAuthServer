<?php namespace exceptions\suspension;

use spitfire\exceptions\PublicException;


class LoginDisabledException extends PublicException
{
	
	private $model;
	
	public function __construct(\user\SuspensionModel$model) {
		$this->model = $model;
		parent::__construct($model->reason, 400, null);
	}
	
	public function getExpiration() {
		return $this->model->expires;
	}
	
	public function getUser() {
		return $this->model->user;
	}
	
	public function getId() {
		return 'SUS-' . $this->model->_id;
	}
	
}