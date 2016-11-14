<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

require_once __DIR__.'/services.common.php';

/**
 * Starts the session the first time some component requests the session service
 * HINT : Already created by FactoryDefault as : Phalcon\Session\Adapter\Files
 */
/*
$di->setShared('session', function () {
	$session = new SessionAdapter();
	$session->start();

	return $session;
});

*/