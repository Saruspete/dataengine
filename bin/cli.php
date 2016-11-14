<?php

# vim: ts=4

use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Loader;

require_once __DIR__.'/docopt.php';
require_once __DIR__.'/CliConsole.php';


$doc = <<<DOC
Phalcon CLI.

Usage:
  cli.php <module> <task> <action> [<params> ...]  [--env=<env>]

Options:
  -h --help			 Show this screen.
  --env=<env>		   Set environment [default: local]
DOC;


//
// Argument parsing
//

$module = 'Frontend';
$task   = 'main';
$action = 'main';
$params = array();

// Uses DocOpt to parse option and manage help
$docopt = new Docopt();
$args = $docopt->handle($doc);
foreach($args as $k => $v) {
	if ($k == '--env') {
		$env = $v;
	}
	elseif (preg_match('/<(.*?)>/', $k, $match)) {
		$k = array_pop($match);
		$$k = $v;
	}
}

// build arguments for CLI
$arguments['module'] = $module;
$arguments['task'] = $task;
$arguments['action'] = $action;
$arguments['params'] = $params;


//
// used in other places of application
//
define('APP_ENV', $env);
define('BASE_PATH', realpath(__DIR__.'/..'));
define('APP_PATH', realpath(BASE_PATH.'/apps'));

// Create our application
$application = new AMConsole();
$application->main();

$di = $application->getDI();

// Register services
require BASE_PATH . '/config/services.cli.php';

// Register our modules
require_once __DIR__.'/../config/modules.php';


// https://forum.phalconphp.com/discussion/4573/cli-task-not-found-when-in-namespace#C15319
/** @var $dispatcher \Phalcon\CLI\Dispatcher */
$dispatcher = $di['dispatcher'];
$dispatcher->setDefaultNamespace('AMPortal\Frontend');
$dispatcher->setNamespaceName('AMPortal\\'.$arguments['module'].'\\Tasks');


//print_r($application);


// Main management
try {
	$application->handle($arguments);
}
catch (\Phalcon\Exception $e) {
	echo "Main error: ", $e->getMessage(), PHP_EOL;
	echo $e->getTraceAsString(), PHP_EOL;
}
