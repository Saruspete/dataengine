<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

// Simple access to test the dataengine
use Phalcon\Mvc\Application;
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

	$loader->registerNamespaces(array(
		'AMPortal'	=> __DIR__.'/../apps/',
	))->register();

	// The debug instance need try/catch to be removed
	$debug = new \Phalcon\Debug();
	$debug->listen();


	$app = new Application();

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

	$di->setShared('view', function () {
    	$view = new \Phalcon\Mvc\View\Simple();
    	$view->setViewsDir('../apps/DataEngine/Views/');
    	return $view;
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

	$app->get('/', function() use ($di) {

		
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



	$app->notFound(function () use ($app) {
		$app->response->setStatusCode(404, "Not Found")->sendHeaders();
		echo "This is crazy, but this page was not found!<br />";
		echo "<h2>Request</h2>";
		echo '$_REQUEST : <pre>', print_r($_REQUEST, true), '</pre>';
		echo '$_SERVER : <pre>', print_r($_SERVER, true), '</pre>';
		echo '<h2>Application parameters</h2>';
		echo '<pre>', print_r($app['router'], true), '</pre>';
	});




	echo '<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>Phalcon PHP Framework</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">';
	$app->handle();
	echo '</div>
        <!-- jQuery (necessary for Bootstraps JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    </body>
</html>';


// Commented out for Phalcon\Debug purpose
/*
} catch (Phalcon\Exception $e) {
    echo "!! Phalcon Exception: ",$e->getMessage(), ' (', $e->getFile(),':',$e->getLine(),')';
    echo '<pre>', $e->getTraceAsString(), '</pre>';

} catch (PDOException $e) {
    echo "!! PDO Exception: ", $e->getMessage();
}
*/
