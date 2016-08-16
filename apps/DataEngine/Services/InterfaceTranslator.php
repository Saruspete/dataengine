<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Services;

use AMPortal\DataEngine\Models\Connection;

interface InterfaceTranslator {
	public function testConnection(Connection $c);
	public function getConnectionUid(Connection $c);
	
}