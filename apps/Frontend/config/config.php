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
		'dbname'      => 'dataengine',
		'charset'     => 'utf8',
	],
	'application' => [
		'viewsDir'    => __DIR__ . '/../Views/',
		'cacheDir'    => BASE_PATH . '/cache/views/',
		'baseUri'     => '/'
	]
]);
