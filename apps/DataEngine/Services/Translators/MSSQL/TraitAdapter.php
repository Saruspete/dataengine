<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Services\Translators\MSSQL;

use AMPortal\DataEngine\Models\Connection;

use AMPortal\DataEngine\Services\Translators\MSSQL\MSSQLDriver as AdapterPdoMSSQL;
use PDO;

trait TraitAdapter {

	protected function _initAdapter() {
		$this->_defaultPort = 1434;
	}

	protected function _createAdapter(Connection $c) {

		// Add port if specified
		$host = $c->hostname;
		if (!empty($c->hostport))
			$host .= ':'.$c->hostport;

		// TODO: use first available database instead
		$base = $c->resource;
/*
		if (empty($base))
			$base = 'mysql';
*/

		return new AdapterPdoMSSQL(array(
			'host'		=> $host,
			'username'	=> $c->username,
			'password'	=> $c->password,
			'dbname'	=> $base,
			/*
			'options'	=> array(
				PDO::ATTR_ERRMODE			=> PDO::ERRMODE_EXCEPTION,
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
			//	PDO::ATTR_CASE               => PDO::CASE_LOWER
			),
			*/
		));

	}


}