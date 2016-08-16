<?php

/**
 *  This file is part of DataEngine, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Services\Translators\MySQL;

use AMPortal\DataEngine\Services\Translators\DiscoverySQL;
use AMPortal\DataEngine\Models\Connection;

use Phalcon\Db\Adapter\Pdo as PdoAdapter;
use Phalcon\Db\Adapter\Pdo\MySQL as PdoMySQLAdapter;
use Phalcon\Db\Index;
use PDO;

/**
 * Discover 
 *
 *
 */


class Discovery extends DiscoverySQL {

	// Use common adapter trait for MySQL
	use TraitAdapter;

	protected function _listDatabases(PdoAdapter $db) {
		$sth = $db->query('SHOW DATABASES WHERE `Database` NOT RLIKE "(performance|information)_schema"');
		return $sth->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	protected function _listTables(PdoAdapter $db, $dbname) {
		return $db->listTables($dbname);
	}

	protected function _listViews(PdoAdapter $db, $dbname) {
		return $db->listViews($dbname);
	}


	protected function _listColumns(PdoAdapter $db, $dbname, $tablename) {
		return $db->describeColumns($tablename, $dbname);
	}

	protected function _listIndexes(PdoAdapter $db, $dbname, $tablename) {
		return $db->describeIndexes($tablename, $dbname);
	}

	protected function _listReferences(PdoAdapter $db, $dbname, $tablename) {
		return $db->describeReferences($tablename, $dbname);
	}

	protected function _getIndexType(Index $idx) {
		$s_type = $idx->getType();
		switch ($s_type) {
			case 'PRIMARY':
			case 'UNIQUE':
				return strtolower($s_type);
				break;

			case '':
				return 'index';
				break;

			default:
				throw new Exception("Unhandled index type: $s_type", 1);
				break;

		}
	}

}