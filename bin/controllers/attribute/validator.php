<?php namespace attribute;

class ValidatorController extends \BaseController
{
	
	public function add(\AttributeModel$attribute) {
		
		$collector  = new AttributeValidatorCollector();
		
		try {
			if (!$this->request->isPost()) { throw new \spitfire\exceptions\HTTPMethodException(); }
			
			$model = db()->table('attribute\validator')->newRecord();
			$model->attribute = $attribute;
			$model->validator = $_POST['validator'];
			$model->settings  = $_POST['arguments'];
			$model->store();
			
			return $this->response->setBody('Redirecting..')
					->getHeaders()->redirect(url('attribute', 'edit', $attribute->_id));
		} 
		catch (\spitfire\exceptions\HTTPMethodException$ex) { /* This is acceptable */ }
		
		$validators = array_filter($collector->getValidators(), function ($e) use ($attribute) {
			return $e->validates() === $attribute->datatype;
		});
		
		$this->view->set('attribute', $attribute);
		$this->view->set('validators', $validators);
		
	}
	
}
