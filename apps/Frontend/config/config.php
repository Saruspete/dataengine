<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

return new \Phalcon\Config([
	'database' => [
		'adapter'     => 'Mysql',
		'host'        => 'localhost',
		'username'    => 'root',
		'password'    => '',
		'dbname'      => 'amportal',
		'charset'     => 'utf8',
	],
	'logger' => [
		'localfile' => [
			'type'		=> 'File',
			'param1'	=> BASE_PATH . '/data/logs/app.'.APP_TYPE.'.'.date('ymd').'.log',
			'param2'	=> null,
			// For levels, see logger.zep https://github.com/phalcon/cphalcon/blob/master/phalcon/logger.zep
			'level'		=> 7, // SPECIAL=9, CUSTOM, DEBUG=7, INFO, NOTICE, WARNING, ERROR, ALERT, CRITICAL, EMERGENCY = 0
			'lineFmt'	=> null,
			'dateFmt'	=> 'y-m-d_H-m-s',

		],
		/*
		'syslog' => [
			'type'		=> 'Syslog',
			'param'		=> 
		]
		*/
	],
	'application' => [
		'viewsDir'    => __DIR__ . '/../Views/',
		'cacheDir'    => BASE_PATH . '/cache/views/',
		'baseUri'     => '/'
	]
]);
