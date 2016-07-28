<?php

// Simple access to test the dataengine
use Phalcon\Mvc\Micro;
use Phalcon\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql;

error_reporting(E_ALL);
ini_set('display_errors', 1);

//try {

	$loader = new Loader();
	
	/*
	$loader->registerDirs(array(
		__DIR__.'/../apps/',
	))->register();
	*/

	$loader->registerNamespaces(array(
		'AMPortal'	=> __DIR__.'/../apps/',
	))->register();

	// The debug instance need try/catch to be removed
	$debug = new \Phalcon\Debug();
	$debug->listen();


	// Micro application instance
	$app = new Micro();

	$di = new FactoryDefault();
	$app->setDi($di);

	// Register our services
	$di->setShared('de_discmgr', function() use ($di) {
		return new \AMPortal\DataEngine\Services\DiscoveryManager($di);
	});

	$di->setShared('de_trstmgr', function() use ($di) {
		return new \AMPortal\DataEngine\Services\TranslatorManager($di);
	});

	$di->setShared('db', function() use ($app) {
		return new Phalcon\Db\Adapter\Pdo\MySQL(
			array(
				'host'		=> "localhost",
				'username'	=> 'root',
				'password'	=> 'PouetCoinLol',
				'dbname'	=> 'dataengine',
			)
		);
	});

	// ////////////////////////////////////////////
	// HTTP Routes

	$app->get('/', function() {
		echo "DataEngine test index page";
	});

	// Discovery test
	$app->get('/discover', function() use ($app) {

		// Target database to discover
		$conn = \AMPortal\DataEngine\Models\Connection::findFirst(1);


		echo '<h1>Result of discovery</h1>';
		echo '<pre>', var_dump($app['de_discmgr']->discover($conn)), '</pre>';

	});

	// Translation test
	$app->get('/translate/{cid:[0-9]+}', function($cid) use ($app) {

//		$collSrc = AMPortal\DataEngine\Models\Collection::findFirst(1);
//		$collDst = AMPortal\DataEngine\Models\Collection::findFirst(2);

// We'll use the same collection as source and destination, just changing the connection
		$collSrc = AMPortal\DataEngine\Models\Collection::findFirst(1);
		$collDst = $collSrc;
		$connSrc = AMPortal\DataEngine\Models\Connection::findFirst($cid);
		$connDst = AMPortal\DataEngine\Models\Connection::findFirst($cid);
		$connDst->resource = "dataengine_test";

		// Translate from source
		echo "<h1>Translation</h1>";
		$app['de_trstmgr']->translate($connSrc, $connDst, $collSrc, $collDst);

	});
	

	$app->notFound(function () use ($app) {
		$app->response->setStatusCode(404, "Not Found")->sendHeaders();
		echo "This is crazy, but this page was not found!<br />";
		echo "<h2>Request</h2>";
		echo '$_REQUEST : <pre>', print_r($_REQUEST, true), '</pre>';
		echo '$_SERVER : <pre>', print_r($_SERVER, true), '</pre>';
		echo '<h2>Application parameters</h2>';
		echo '<pre>', print_r($app['router'], true), '</pre>';
	});


	$app->handle();

// Commented out for Phalcon\Debug purpose
/*
} catch (Phalcon\Exception $e) {
    echo "!! Phalcon Exception: ",$e->getMessage(), ' (', $e->getFile(),':',$e->getLine(),')';
    echo '<pre>', $e->getTraceAsString(), '</pre>';

} catch (PDOException $e) {
    echo "!! PDO Exception: ", $e->getMessage();
}
*/