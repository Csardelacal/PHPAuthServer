<?php

use spitfire\Model;
use spitfire\model\adapters\FloatAdapter;
use spitfire\model\Field;
use spitfire\storage\database\Query;
use spitfire\validation\ValidationError;

class FloatField extends Field
{
	
	protected $unsigned;
	
	
	public function __construct( $unsigned = false) {
		$this->unsigned = $unsigned;
	}
	
	public function isUnsigned() {
		return $this->unsigned;
	}

	public function getDataType() {
		return Field::TYPE_FLOAT;
	}
	
	public function validate($value) {
		if (!is_numeric($value)) { return new ValidationError(_t('err_not_numeric', $this->length)); }
		else { return parent::validate($value); }
	}

	public function getAdapter(Model $model) {
		return new FloatAdapter($this, $model);
	}

	public function getConnectorQueries(Query $parent) {
		return Array();
	}

}
