<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Services\Translators\MSSQL;

use AMPortal\DataEngine\Services\Translators\DiscoverySQL;
use AMPortal\DataEngine\Models\Connection;

use Phalcon\Db\Adapter\Pdo as PdoAdapter;
use AMPortal\DataEngine\Services\Translators\MSSQL as PdoMSSQLAdapter;
use Phalcon\Db\Index;
use PDO;

/**
 * Discover 
 *
 *
 */


class Discovery extends DiscoverySQL {
	
	// Use common adapter trait for MSSQL
	use TraitAdapter;

	protected $_escapeCharLeft = "[";
	protected $_escapeCharRight = "]";


	//
	// List database contents and structures
	//

	protected function _listDatabases(PdoAdapter $db) {
		$sth = $db->query('SELECT name FROM [sys].[databases]');
		return $sth->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	protected function _listSchemas(PdoAdapter $db, $dbname) {
		sth = $db->query("SELECT [SCHEMA_NAME] FROM [INFORMATION_SCHEMA].[SCHEMATA] WHERE [CATALOG_NAME] = '".$dbname."'");
		return $sth->fetchAll(PDO::FETCH_COLUMN, 0);
		//return array('dbo');
	}

	protected function _listTables(PdoAdapter $db, $dbname, $schemaname) {
		return $db->listTables($schemaname);
	}

	protected function _listViews(PdoAdapter $db, $dbname, $schemaname) {
		return $db->listViews($schemaname);
	}

	protected function _listColumns(PdoAdapter $db, $dbname, $schemaname, $tablename) {
		return $db->describeColumns($tablename, $schemaname);
	}

	protected function _listIndexes(PdoAdapter $db, $dbname, $schemaname, $tablename) {
		return $db->describeIndexes($tablename, $schemaname);
	}

	protected function _listReferences(PdoAdapter $db, $dbname, $schemaname, $tablename) {
		return $db->describeReferences($tablename, $schemaname);
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