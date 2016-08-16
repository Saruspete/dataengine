<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Services;

use AMPortal\DataEngine\Models\Connection;


class DiscoveryManager extends BaseManager {

	/**
	 *
	 *
	 */
	public function discover(Connection $c, $refresh = true) {

		// Get the object according to the type
		$o_discovery = $this->_createDiscovery($c);

		if (!$o_discovery->testConnection($c)) {
			throw new \Phalcon\Exception("Unable to connect using provided connection");
		}

		// Call the discovery
		$o_discovery->discoverStructure($c);
		$o_discovery->discoverRelations($c);

		return true;
	}

}