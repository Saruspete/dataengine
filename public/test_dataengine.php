<?php

// Simple access to test the dataengine
use Phalcon\Mvc\Micro;
use Phalcon\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\Profiler as DbProfiler;
use Phalcon\Logger\Adapter\File as Logger;
use Phalcon\Events\Manager as EventsManager;

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

	// Dependency Injection
	$di = new FactoryDefault();
	$app->setDi($di);


	// Discovery Manager
	$di->setShared('de_discmgr', function() use ($di) {
		return new \AMPortal\DataEngine\Services\DiscoveryManager($di);
	});

	// Translator Manager
	$di->setShared('de_trstmgr', function() use ($di) {
		return new \AMPortal\DataEngine\Services\TranslatorManager($di);
	});

	// DB Profiler
	$di->setShared('dbProfiler', function () {
		return new DbProfiler();
	});

	// DB handler
	$di->setShared('db', function() use ($app, $di) {

		$connection = new Phalcon\Db\Adapter\Pdo\MySQL(
			array(
				'host'		=> "localhost",
				'username'	=> 'root',
				'password'	=> 'PouetCoinLol',
				'dbname'	=> 'dataengine',
			)
		);

		$eventsManager = new EventsManager();
		$profiler      = $di->getDbProfiler();
		$logger        = new Logger( __DIR__ ."/../../logs/app/db.log");

    	// Listen all the database events
		$eventsManager->attach('db', function($event, $connection) use ($logger, $profiler) {
			if ($event->getType() == 'beforeQuery') {

				// Profiler
				$profiler->startProfile($connection->getSQLStatement());

				// Logger
				$sqlVariables = $connection->getSQLVariables();
				if (count($sqlVariables)) {
					$logger->info($connection->getSQLStatement() . ' ' . join(', ', $sqlVariables));
				} else {
					$logger->info($connection->getSQLStatement());
				}
			}

			if ($event->getType() == 'afterQuery') {
				$profiler->stopProfile();
			}
		});

		//Assign the eventsManager to the db adapter instance
		$connection->setEventsManager($eventsManager);

		return $connection;
	});

	// ////////////////////////////////////////////
	// HTTP Routes

	$app->get('/', function() {
		echo "DataEngine test index page. Test links :<br /><ul>";
		echo '<li><a href="/testde/discover/list">/discover/list</a> : List connections already used for discovery</li>';
		echo '<li><a href="/testde/discover/1">/discover/[0-9]+</a> : Use an existing connection</li>';
		echo '<li><a href="/testde/discover/host/user/pass/base">/discover/$host/$user/$pass[/$base]</a> : Discover a database from URL provided data</li>';
		echo '<li><a href="/testde/translate/1">/translate/1</a> : Translate Collections</li>';
		echo '</ul>';
	});

	// Discovery list
	$app->get('/discover/list', function() use ($app) {

		// Target database to discover
		$a_conns = \AMPortal\DataEngine\Models\Connection::find();

		echo '<h1>Connections listing</h1>';
		echo '<table><tr>';
		echo '<th>Host</th><th>User</th><th>Pass</th><th>Base</th><th>Test</th>';
		echo '</tr>';

		foreach ($a_conns as $o_conn) {
			echo '<tr>',
				'<td>', $o_conn->hostname, '</td>',
				'<td>', $o_conn->username, '</td>',
				'<td>', $o_conn->password, '</td>',
				'<td>', $o_conn->resource, '</td>',
				'<td><a href="/testde/discover/', $o_conn->getId(),'">Test</a></td>',
				'</tr>';
		}

	});

	// Discover user provided credentials
	$app->get('/discover/{host}/{user}/{pass}/{base}', function($host, $user, $pass, $base = false) use ($app) {

		// Target database to discover
		$conn = \AMPortal\DataEngine\Models\Connection::findFirst(array(
			'hostname="'.$host.'" AND username="'.$user.'" AND password="'.$pass.'" AND resource="'.$base.'"'
		));

		if ($conn) {
			echo 'This connection is already registered. Please go to <a href="/testde/discover/', $conn->getId(),'">discover/', $conn->getId(),'</a>';

		}
		else {
		
			$conn = new \AMPortal\DataEngine\Models\Connection();
			$conn->type = 'MySQL';
			$conn->hostname = $host;
			$conn->username = $user;
			$conn->password = $pass;
			$conn->resource = $base;
			//$conn->name = 'URL Provided :'.$user.'@'.$host.'/'.$base;
			$conn->name = $conn->getDsn('PEAR');

			$conn->save();

			echo '<h1>Result of discovery</h1>';
			echo '<pre>', var_dump($app['de_discmgr']->discover($conn)), '</pre>';
		}
	});

	// Discover already saved connection
	$app->get('/discover/{cid:[0-9]+}', function($cid) use ($app) {

		$conn = \AMPortal\DataEngine\Models\Connection::findFirst($cid);

		echo '<h1>Result of discovery</h1>';
		echo '<pre>', var_dump($app['de_discmgr']->discover($conn)), '</pre>';

	});




	// Translation test
	$app->get('/translate/{cid:[0-9]+}', function($cid) use ($app, $di) {

//		$collSrc = AMPortal\DataEngine\Models\Collection::findFirst(1);
//		$collDst = AMPortal\DataEngine\Models\Collection::findFirst(2);

// We'll use the same collection as source and destination, just changing the connection
		$connSrc = AMPortal\DataEngine\Models\Connection::findFirst($cid);
		$connDst = AMPortal\DataEngine\Models\Connection::findFirst($cid);
		$connDst->resource = "dataengine_test";

		$collSrc = AMPortal\DataEngine\Models\Collection::findFirst(1);
		$collDst = AMPortal\DataEngine\Models\Collection::findFirst(2);

//		echo "<pre>", var_dump($collSrc), "</pre>";

		// Translate from source
		echo "<h1>Translation</h1>";
		$app['de_trstmgr']->translate($connSrc, $connDst, $collSrc, $collDst);

/*
		// Get the generated profiles from the profiler
		$profiles = $di->get('dbProfiler')->getProfiles();

		foreach ($profiles as $profile) {
			echo "SQL Statement: ", $profile->getSQLStatement(), "\n";
			echo "Start Time: ", $profile->getInitialTime(), "\n";
			echo "Final Time: ", $profile->getFinalTime(), "\n";
			echo "Total Elapsed Time: ", $profile->getTotalElapsedSeconds(), "\n";
		}
*/

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