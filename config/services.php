<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Flash\Direct as Flash;

use AMPortal\Frontend\Library\Elements;

// Candidates for removal as default of FactoryDefault
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;


/**
 * Shared configuration service
 */
$di->setShared('config', function () {
	return include APP_PATH . "/Frontend/config/config.php";
});


/**
 * Registering a router
 * HINT : Already created by FactoryDefault as : Phalcon\Mvc\Router
 */
$di->setShared('router', function () {
	$router = new Router();

	$router->setDefaultModule('Frontend');
	$router->setDefaultNamespace('AMPortal\Frontend\Controllers');

	return $router;
});


/**
 * Set the default namespace for dispatcher
 * HINT : Already created by FactoryDefault as : Phalcon\Mvc\Dispatcher
 */
$di->setShared('dispatcher', function() {
	$dispatcher = new Phalcon\Mvc\Dispatcher();
	$dispatcher->setDefaultNamespace('AMPortal\Frontend\Controllers');
	return $dispatcher;
});


/**
 * The URL component is used to generate all kinds of URLs in the application
 * HINT : Already created by FactoryDefault as : Phalcon\Mvc\Url
 */
$di->setShared('url', function () use ($di) {
	$config = $di->getConfig();

	$url = new UrlResolver();
	$url->setBaseUri($config->application->baseUri);

	return $url;
});

/**
 * Setting up the view component
 */
$di->setShared('view', function () use ($di) {
	$config = $di->getConfig();

	$view = new View();
	$view->setViewsDir($config->application->viewsDir);

	$view->registerEngines([
		'.volt' => function ($view, $di) {
			$config = $di->getConfig();

			$volt = new VoltEngine($view, $di);
			$volt->setOptions([
				'compiledPath' => $config->application->cacheDir,
				'compiledSeparator' => '_',

				// For dev env
				'stat' => true,
				'compileAlways' => true  
			]);
			

			return $volt;
		},
		'.phtml' => 'Phalcon\Mvc\View\Engine\Php'
	]);

	return $view;
});


/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () use ($di) {
	$config = $di->getConfig();

	$dbConfig = $config->database->toArray();
	$adapter = $dbConfig['adapter'];
	unset($dbConfig['adapter']);

	$class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;

	return new $class($dbConfig);
});


/**
 * Starts the session the first time some component requests the session service
 * HINT : Already created by FactoryDefault as : Phalcon\Session\Adapter\Files
 */
$di->setShared('session', function () {
	$session = new SessionAdapter();
	$session->start();

	return $session;
});

/**
 * Register the session flash service with the Twitter Bootstrap classes
 * HINT : Already created by FactoryDefault as : Phalcon\Flash\Direct
 */
$di->set('flash', function () {
	return new Flash([
		'error'   => 'alert alert-danger',
		'success' => 'alert alert-success',
		'notice'  => 'alert alert-info',
		'warning' => 'alert alert-warning'
	]);
});


// TODO : migrate this helper from INVO to a more clean name and parsing
$di->setShared('elements', function() {
	return new AMPortal\Frontend\Library\Elements();
});

//
//
// Candidates for Removal
//
//

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 * HINT : Already created by FactoryDefault as : Phalcon\Mvc\Model\Metadata\Memory
 */
$di->setShared('modelsMetadata', function () {
	return new MetaDataAdapter();
});
