<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\Application;

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', realpath('..'));
define('APP_PATH', realpath(BASE_PATH.'/apps'));

//try {

	// The debug instance need try/catch to be removed
	$debug = new \Phalcon\Debug();
	$debug->listen();

	$di = new FactoryDefault();

	/**
	 * Include services
	 */
	require BASE_PATH . '/config/services.php';

	/**
	 * Handle the request
	 */
	$application = new Application($di);
	
	// Register the Frontend for every request going from HTTP
	$loader = new Loader();
	$loader->registerNamespaces(array(
		'AMPortal\Frontend'	 => APP_PATH . '/Frontend/',
	));
	$loader->register();


	/**
	 * Include modules
	 */
	require BASE_PATH . '/config/modules.php';

	/**
	 * Include routes
	 */
	require BASE_PATH . '/config/routes.php';


	echo $application->handle()->getContent();

/*
} catch (\Exception $e) {
	echo $e->getMessage(). '<br>';
	echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
*/
