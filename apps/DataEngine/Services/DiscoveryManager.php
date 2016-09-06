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
		$a_results = array();
		$a_results['structure'] = $o_discovery->discoverStructure($c);
		$a_results['relations'] = $o_discovery->discoverRelations($c);

		// TODO: Add more details about discovery...
		return $a_results;
	}

	/**
	 * 
	 * @return array The list of placeholders
	 */
	public function testConnection(Connection $c) {
		$o_discovery = $this->_createDiscovery($c);
		return $o_discovery->testConnection($c);
	}

}