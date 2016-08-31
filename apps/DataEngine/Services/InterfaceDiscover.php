<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Services;

use AMPortal\DataEngine\Models\Connection;

interface InterfaceDiscover {
	
	public function getConnectionUid(Connection $cn);
	public function testConnection(Connection $cn);

	public function discoverStructure(Connection $cn, $shallUpdate = false);
	public function discoverRelations(Connection $cn);


}