<?php

class AMConsole extends \Phalcon\CLI\Console {
/*
	public function __construct() {
		$loader = new \Phalcon\Loader();
		$loader->registerNamespaces($namespaces['base']);

		// register the installed modules
		$this->registerModules(array(
			'v1' => array(
				'className' => 'Kapi\V1\Module',
				'path' => __DIR__  . '/../apps/kapi/v1/Module.php'
			),
			'admin' => array(
				'className' => 'Kapi\Admin\Module',
				'path' => __DIR__ . '/../apps/kapi/admin/Module.php'
			),
		));

		$loader->register();
	}
*/

	public function main() {
		$di = new \Phalcon\DI\FactoryDefault\CLI();

		// registering a router
		$di->set('router', function(){
			$router = new \Phalcon\CLI\Router();

			return $router;
		});

		// registering a dispatcher
		$di->set('dispatcher', function () use($di) {

			// obtain the standard eventsManager from the DI
			$eventsManager = $di->getShared('eventsManager');

			$eventsManager->attach("dispatch:beforeDispatchLoop", function($event, $dispatcher) {
				$dispatcher->setActionName(\Phalcon\Text::camelize($dispatcher->getActionName()));
			});

			$dispatcher = new \Phalcon\CLI\Dispatcher();
			// bind the EventsManager to the Dispatcher
			$dispatcher->setEventsManager($eventsManager);

			return $dispatcher;
		});

		$this->setDI($di);
	}
}


