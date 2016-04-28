<?php namespace mail;

use EmailModel;

interface TransportInterface
{
	
	function deliver(EmailModel$model);
	
}
