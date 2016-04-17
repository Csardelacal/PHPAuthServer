<?php

class AttributeBean extends CoffeeBean
{
	
	public function definitions() {
		$this->field('_id',      'Programmer friendly ID');
		$this->field('name',     'Name');
		$this->field('datatype', 'Data type');
		$this->field('required', 'Required');
		$this->field('default',  'Default value');
		$this->field('readable', 'Read permissions');
		$this->field('writable', 'Write permissions');
	}

}
