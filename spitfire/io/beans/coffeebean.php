<?php

use spitfire\exceptions\PrivateException;
use spitfire\exceptions\PublicException;
use spitfire\io\beans\BooleanField;
use spitfire\io\beans\ChildBean;
use spitfire\io\beans\DateTimeField;
use spitfire\io\beans\EnumField;
use spitfire\io\beans\Field;
use spitfire\io\beans\FileField;
use spitfire\io\beans\IntegerField;
use spitfire\io\beans\LongTextField;
use spitfire\io\beans\ManyToManyField;
use spitfire\io\beans\ReferenceField;
use spitfire\io\beans\TextField;
use spitfire\io\beans\UnSubmittedException;
use spitfire\io\PostTarget;
use spitfire\io\renderers\Renderable;
use spitfire\io\renderers\RenderableFieldGroup;
use spitfire\io\renderers\RenderableForm;
use spitfire\io\XSSToken;
use spitfire\Model;
use spitfire\model\Field as Field2;
use spitfire\storage\database\Table;
use spitfire\validation\ValidationException;
use spitfire\validation\ValidationRule;
use spitfire\validation\ValidatorInterface;

/**
 * A Bean is the equivalent to a Model for users. Instead of generating SQL and
 * reading resultsets a Bean generates forms and reads the POST data they 
 * generate. This allows to automate data i/o tasks with users and quickly 
 * produce applications that interact with the user.
 * 
 * @author César de la Cal <cesar@magic3w.com>
 */
abstract class CoffeeBean extends PostTarget implements RenderableForm, RenderableFieldGroup, ValidatorInterface
{
	
	private $fields = Array();
	private $record;
	private $parent;
	private $table;
	private $xss;
	
	public $name;
	public $model;
	
	/**
	 * Create a new bean. This allows to generate forms to receive data from a 
	 * client, it requires a Table to know which model it shall work on.
	 * 
	 * @param Table $table
	 */
	public final function __construct(Table$table = null) {
		$this->table = $table;
		$this->xss   = new XSSToken();
		$this->definitions();
	}
	
	/**
	 * Creates the fields for this bean. By doing so the bean knows which fields
	 * it can present to the user to input data.
	 */
	abstract public function definitions();

	/**
	 * This function informs you about the status of the bean. This status
	 * can take three different values, the bean returns a value that is considered
	 * safe or the bean throws one of two possible exceptions (validation or 
	 * unsubmitted) allowing you to react accordingly.
	 * 
	 * This function is meant to aid you taking the decision whether the bean
	 * should display a form or store the data.
	 * 
	 * @throws ValidationException|UnSubmittedException If the bean cannot be stored
	 */
	public function validate() {
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			
			if (!$this->xss->verify($_POST['_XSS_'])) { throw new PublicException('XSS Attack', 403); }
			
			if (!$this->isOk()) { throw new ValidationException('Validation failed', 1604172344, $this->getMessages()); }
		}
		else { throw new UnSubmittedException(); }
	}

	public function addRule(ValidationRule $rule) {
		throw new PrivateException('You cannot add validation rules to beans');
	}

	public function getMessages() {
		$messages = Array();
		
		foreach ($this->fields as $field) {
			$messages = array_merge($messages, $field->getMessages());
		}
		
		return $messages;
	}

	public function isOk() {
		$ok = true;
		
		foreach ($this->fields as $field) {
			$ok = $field->isOk() && $ok;
		}
		
		return $ok;
	}
	
	/**
	 * Updates the data stored in the current record (Model) so you can use it or 
	 * write it to the database. In case the record has additional validation 
	 * methods you will have to run those first. 
	 * 
	 * @param Model $record The record to be updated (if not defined by setRecord)
	 * @return \Model
	 */
	public function updateDBRecord(Model$record = null) {
		if ($record === null) { $record = $this->record; }
		
		if ($this->table && $record) {
			$fields = $this->fields;
			foreach ($fields as $field) {
				$value = $field->getValue();
				$record->{$field->getFieldName()} = $value;
			}
		}
		$this->clearPostData();
		return $record;
	}
	
	public function setDBRecord($record) {
		if ($record instanceof Model || is_null($record)) {
			$this->record = $record;
		}
	}
	
	/**
	 * Returns the current record this bean is representing. This will be used to
	 * populate the form in case there is no data being sent to the form.
	 * 
	 * @return \Model
	 */
	public function getRecord() {
		return $this->record;
	}
	
	/**
	 * Creates a new field for the bean.
	 * 
	 * @param Field2 $field
	 * @param string $caption
	 * @return Field
	 */
	public function field($field, $caption) {
		$logical = $this->table->getModel()->getField($field);
		
		if (!$logical) { throw new PrivateException('No field ' . $field . ' in ' . $this->table->getModel()->getName()); }
		
		$suggested = $logical->getBeanField($this, $logical, $caption);
		if ($suggested !== null) {return $this->fields[$field] = $suggested;}
		
		switch($logical->getDataType()) {
			case Field2::TYPE_STRING:
			case Field2::TYPE_LONG:
				if ($logical instanceof \EnumField)
					{ return $this->fields[$field] = new EnumField($this, $logical, $caption); }
				else
					{ return $this->fields[$field] = new TextField($this, $logical, $caption); }
			case Field2::TYPE_INTEGER:
				return $this->fields[$field] = new IntegerField($this, $logical, $caption);
			case Field2::TYPE_DATETIME:
				return $this->fields[$field] = new DateTimeField($this, $logical, $caption);
			case Field2::TYPE_TEXT:
				return $this->fields[$field] = new LongTextField($this, $logical, $caption);
			case Field2::TYPE_FILE:
				return $this->fields[$field] = new FileField($this, $logical, $caption);
			case Field2::TYPE_REFERENCE:
				return $this->fields[$field] = new ReferenceField($this, $logical, $caption);
			case Field2::TYPE_CHILDREN:
				return $this->fields[$field] = new ChildBean($this, $logical, $caption);
			case Field2::TYPE_BRIDGED:
				return $this->fields[$field] = new ManyToManyField($this, $logical, $caption);
			case Field2::TYPE_BOOLEAN:
				return $this->fields[$field] = new BooleanField($this, $logical, $caption);
		}
	}
	
	public function getFormFields() {
		$fields = $this->fields;
		if (!$this->parent) { $fields[] = $this->xss; }
		return $fields;
	}
	
	public function getFields() {
		$fields = $this->fields;
		return $fields;
	}
	
	public function getField($name) {
		return $this->fields[$name];
	}
	
	/**
	 * Returns the table using this bean to generate it's forms.
	 * 
	 * @return Table
	 */
	public function getTable() {
		return $this->table;
	}
	
	public function getName() {
		if ($this->name) return $this->name;
		else return substr( get_class ($this), 0, - strlen('Bean'));
	}
	
	public function setName($name) {
		$this->name = $name;
	} 

	public function makeForm($renderer) {
		return $renderer->renderForm($this);
	}
	
	public function makeList($renderer, $records) {
		return $renderer->renderList($this, $records);
	}
	
	public function setParent($field) {
		$this->parent = $field;
	}
	
	public function getParent() {
		return $this->parent;
	}
	
	public function getPostId() {
		if ($this->parent) { return $this->parent->getPostId() . "[{$this->getName()}]";}
		return $this->getName();
	}
	
	public function getEnforcedFormRenderer() {
		return null;
	}
	
	public function getEnforcedFieldRenderer() {
		return null;
	}
	
	public function getVisibility() {
		return Renderable::VISIBILITY_ALL;
	}
	
	public function getAction() {
		return '';
	}
	
	public function getCaption() {
		return $this->getName();
	}
	
	/**
	 * Returns the field that handles a certain post handle. This allows you to 
	 * quickly cascade data down into the fields where it belongs.
	 * 
	 * @param string $name
	 * @return Field That contains the said target
	 */
	public function getPostTargetFor($name) {
		return $this->getField($name);
	}
	
	/**
	 * Returns the value that this Bean would hold in case it was stored. In order
	 * to keep the originally submitted data safe and avoid wrong data being wrongly
	 * written into the system it clones the record before altering it's data.
	 * 
	 * @return \Model
	 */
	public function getValue() {
		$record = clone $this->record;
		$this->updateDBRecord($record);
		return $record;
	}
	
	public function readPost() {
		$this->setPostData($_POST[$this->getName()]);
	}

	/**
	 * Returns an instance of a required bean.
	 *
	 * @param string $name The classname of the bean without Bean at the end of
	 *                     the string.
	 *
	 * @return CoffeeBean
	 * @throws PrivateException
	 */
	public static function getBean($name) {
		#Create a camel cased string for the class
		$class_name = ucfirst($name) . 'Bean';
		
		#Check if it exists and instance
		if (class_exists($class_name)) {
			return new $class_name();
		}
		else throw new PrivateException('Bean not found');
	}

}
