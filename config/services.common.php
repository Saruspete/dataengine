<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

// Candidates for removal as default of FactoryDefault
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Logger;
use Phalcon\Logger\Multiple as LoggerMultiple;
use Phalcon\Logger\Adapter\File as LoggerFile;
use Phalcon\Logger\Adapter\Syslog as LoggerSyslog;
use Phalcon\Logger\Formatter\Line as LoggerLine;

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

/**
 * Logging for CLI and WEB
 */
$di->setShared('logger', function() use ($di) {
	$oConfig = $di->getConfig();
	$aLogConfig = $oConfig->logger->toArray();

	$oLoggerMulti = new LoggerMultiple();

	foreach ($aLogConfig as $sAdapter=>$aConf) {

		// Create the object
		//$sLogType = "Logger".$aConf['type'];
		$sLogType = "Phalcon\\Logger\\Adapter\\".$aConf['type'];

		$mParam1 = (isset($aConf['param1'])) ? $aConf['param1'] : null;
		$mParam2 = (isset($aConf['param2'])) ? $aConf['param2'] : null;

		$oLogTmp = new $sLogType($mParam1, $mParam2);
		//$oLogTmp = new LoggerFile($mParam1, $mParam2);

		// Set the log level
		if ($aConf['level'])
			$oLogTmp->setLogLevel($aConf['level']);

		// Change the line format
		if ($aConf['lineFmt'] || $aConf['dateFmt']) {
			$sLineFmt = $aConf['lineFmt'];
			$sDateFmt = $aConf['dateFmt'];
			$oFmt = new LoggerLine($sLineFmt, $sDateFmt);
			$oLogTmp->setFormatter($oFmt);
		}

		// Add it to the MultiLogger
		$oLoggerMulti->push($oLogTmp);
	}

	return $oLoggerMulti;
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
