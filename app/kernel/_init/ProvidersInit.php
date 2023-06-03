<?php namespace magic3w\phpas\kernel\_init;

use magic3w\phpas\providers\CommandServiceProvider;
use Psr\Container\ContainerInterface;
use spitfire\contracts\core\kernel\InitScriptInterface;
use spitfire\core\service\Provider;

/*
 * Copyright (C) 2021 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

/**
 * The init script of any servive provider allows it to use the components
 * that it registered (and potentially the ones other components registered)
 * to make the component work.
 *
 * Here it's recommended that your component publishes resources it may need,
 * initialize storage, or data so the main application can work.
 *
 * Please note that the order in which the register calls are invoked is not
 * guaranteed. Checking that the components you need are properly intialized
 * is very recommended.
 *
 * If your component depends on a component that is not yet intialized you can
 * manually do so, please make sure that the component is capable of handling
 * being invoked multiple times.
 */
class ProvidersInit implements InitScriptInterface
{
	/**
	 * @todo This should be config.
	 * @var string[]
	 */
	public const PROVIDERS = [
		CommandServiceProvider::class
	];
	
	public function exec(ContainerInterface $container): void
	{
		
		$providers = self::PROVIDERS;
		
		foreach ($providers as $name) {
			/**
			 * @var Provider $provider
			 */
			$provider = $container->get($name);
			$provider->init($container);
		};
	}
}
