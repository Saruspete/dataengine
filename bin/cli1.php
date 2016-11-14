<?php

# vim: ts=4

use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Loader;



// Using the CLI factory default services container
$di = new CliDI();



/**
 * Register the autoloader and tell it to register the tasks directory
 */
$loader = new Loader();

// Register custom commands
$loader->registerDirs(
	glob(__DIR__.'/../apps/*/tasks')
);

$loader->register();



// Load the configuration file (if any)
$configFile = __DIR__ . "/config/config.php";

if (is_readable($configFile)) {
	$config = include $configFile;

	$di->set("config", $config);
}



// Create a console application
$console = new ConsoleApp();

// Register the DI and the Console to eachother
$console->setDI($di);
$di->setShared("console", $console);


/**
 * Process the console arguments
 */
$arguments = [];
foreach ($argv as $k=>$arg) {
	if ($k === 1) {
		$arguments["task"] = $arg;
	} elseif ($k === 2) {
		$arguments["action"] = $arg;
	} elseif ($k >= 3) {
		$arguments["params"][] = $arg;
	}
}

try {
	// Handle incoming arguments
	$console->handle($arguments);
} catch (\Phalcon\Exception $e) {
	echo $e->getMessage();

	exit(255);
}
