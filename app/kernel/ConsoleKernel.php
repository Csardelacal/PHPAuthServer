<?php namespace magic3w\phpas\kernel;

use magic3w\phpas\kernel\_init\ProvidersInit;
use magic3w\phpas\kernel\_init\ProvidersRegister;
use spitfire\core\kernel\ConsoleKernel as CoreConsoleKernel;

class ConsoleKernel extends CoreConsoleKernel
{
	
	
	/**
	 * The list of init scripts that need to be executed in order for the kernel to
	 * be usable.
	 *
	 * @return string[]
	 */
	public function initScripts(): array
	{
		return [
			ProvidersRegister::class,
			ProvidersInit::class,
		];
	}
}
