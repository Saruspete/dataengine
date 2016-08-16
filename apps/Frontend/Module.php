<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\Frontend;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\ModuleDefinitionInterface;


class Module implements ModuleDefinitionInterface {
	/**
	 * Registers an autoloader related to the module
	 *
	 * @param DiInterface $di
	 */
	public function registerAutoloaders(DiInterface $di = null) {

		$loader = new Loader();

		$loader->registerNamespaces([
			'AMPortal\Frontend' => __DIR__.'/',
		]);

		$loader->register();
	}

	/**
	 * Registers services related to the module
	 *
	 * @param DiInterface $di
	 */
	public function registerServices(DiInterface $di) {
		/**
		 * Read configuration
		 */
		//$config = include APP_PATH . "/apps/DataEngine/config/config.php";


	}
}
