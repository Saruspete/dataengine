<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

// Candidates for removal as default of FactoryDefault
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;


/**
 * Shared configuration service
 */
$di->setShared('config', function () {
	return include APP_PATH . "/Frontend/config/config.php";
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
