<?php

use settings\DefaultSettings;
use spitfire\Model;
use spitfire\storage\database\Schema;

class SysSettingModel extends Model
{
	
	public function definitions(Schema $schema) {
		unset($schema->_id);
		
		$schema->key       = new StringField(50);
		$schema->value     = new TextField();
		$schema->changedby = new Reference('user');
		$schema->changed   = new IntegerField();
		
		$schema->key->setPrimary(true);
	}
	
	public static function getValue($for) {
		$v = db()->table('SysSetting')->get('key', $for)->fetch();
		
		if ($v) { return $v->value; }
		else    { return self::setValue($for, DefaultSettings::get($for)); }
	}
	
	public static function setValue($key, $to) {
		$record = db()->table('SysSetting')->newRecord();
		$record->key       = $key;
		$record->value     = $to;
		$record->changedby = null;
		$record->changed   = time();
		$record->store();
		
		return $to;
	}
}
