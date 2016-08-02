<?php

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