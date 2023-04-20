<?php namespace spitfire\mvc;

use spitfire\core\Context;
use spitfire\exceptions\FileNotFoundException;
use spitfire\exceptions\PrivateException;
use spitfire\io\template\Layout;
use spitfire\io\template\Template;

class View extends MVC
{
	private $data = Array();
	
	private $template;
	private $layout;
	private $extension;
	
	/**
	 * Creates a new view. The view allows to present the data your application 
	 * manages in a consistent way and manage and locate the templates the app
	 * needs.
	 * 
	 * @param \spitfire\Context $context
	 */
	public function __construct(Context$context) {
		
		parent::__construct($context);
		
		#Get the answer format
		$this->extension = $context->request->getPath()->getFormat();
		
		#Get the variables needed for the creation of a template
		$basedir    = $this->app->getTemplateDirectory();
		$controller = strtolower(implode(DIRECTORY_SEPARATOR, $this->app->getControllerURI($this->controller)));
		$action     = $this->action;
		$extension  = $this->extension === 'php'? '' : '.' . $this->extension;
		
		#Create the templates for the layout and the template
		$this->template = new Template([
			"{$basedir}{$controller}/{$action}{$extension}.php",
			"{$basedir}{$controller}{$extension}.php"
		]);
		
		$this->layout = new Layout([
			"{$basedir}{$controller}/layout{$extension}.php",
			"{$basedir}layout{$extension}.php"
		]);
	}
	
	/**
	 * Defines a variable inside the view.
	 * @param String $key
	 * @param mixed $value
	 */
	public function set($key, $value) {
		$this->data[$key] = $value;
		return $this;
	}
	
	/**
	 * Sets the file to be used by the template system. Please note that it can
	 * accept either the full patch to the file (like bin/templates/controller/action.php)
	 * or just the path relative to the template directory.
	 * 
	 * Using the relative Path to the template directory would be a recommended 
	 * practice in order to reduce the need to change your coding when changing
	 * your directory structure.
	 * 
	 * If the file is provided with no extension, this method will consider the
	 * standard extension scheme for views inside spitfire
	 * 
	 * @param string $fileName
	 * @throws FileNotFoundException
	 */
	public function setFile ($fileName) {
		$basedir    = $this->app->getTemplateDirectory();
		$extension  = ($this->extension === 'php'? '' : '.' . $this->extension) . '.php';
		
		$this->template->setFile([
			$fileName,
			"{$fileName}{$extension}",
			"{$basedir}{$fileName}",
			"{$basedir}{$fileName}{$extension}",
		]);
	}
	
	public function setLayoutFile($filename) {
		$filename = $this->app->getTemplateDirectory() . $filename;
		
		if (file_exists($filename)) { $this->layout = new Layout($filename); }
		else { throw new FileNotFoundException('File ' . $filename . ' not found. View can\'t use it as layout'); }
	}

	public function element($file) {
		$filename = $this->app->getTemplateDirectory() . 'elements/' . $file . '.php';
		if (!file_exists($filename)) throw new PrivateException('Element ' . $file . ' missing');
		return new ViewElement($filename, $this->data);
	}
	
	public function setRenderTemplate($set) {
		if ($set === false) {
			$this->template = null;
		}
	}
	
	public function setRenderLayout($set) {
		$this->render_layout = $set;
	}

	public function render () {
		#If the template is not to be rendered at all. Use this.
		if (!$this->template) { echo $this->data['_SF_DEBUG_OUTPUT']; return; }
		
		try {
			$output = $this->template->render($this->data);
			
			if ($this->layout && $this->layout->renderable()) {
				$output = $this->layout->content($output)->render($this->data);
			}
			
			return $output;
		}
		catch (FileNotFoundException$e) {
			#Consider that a missing template file that should be rendered is an error
			throw new PrivateException('Missing template file for ' . get_class($this->controller) . '::' . $this->action, 1806011508, $e); 
		}
	}
	
	public function css($add = null) {
		if ($add) { $this->css->add ($add); }
		else      { return $this->css; }
	}
	
	public function js($add = null) {
		if ($add) $this->js->add ($add);
		else return $this->js;
	}
	
}