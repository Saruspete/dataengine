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
		$o_discovery = $this->_createDiscovery($c->type);

		if (!$o_discovery->testConnection($c)) {
			throw new \Phalcon\Exception("Unable to connect using provided connection");
			return false;
		}

		// Call the discovery
		$o_discovery->discoverStructure($c);
		$o_discovery->discoverRelations($c);

		return true;
	}

	/**
	 * Discover the table structures
	 *
	 */
	public function discoverStructure(Connection $c, $discoveryName) {

		
	}

	/**
	 * Discover the relationships between placeholders 
	 *
	 *
	 */
	public function discoverRelations(Connection $c) {

	}

}