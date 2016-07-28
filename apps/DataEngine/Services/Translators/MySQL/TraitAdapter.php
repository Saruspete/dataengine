<?php

namespace AMPortal\DataEngine\Services\Translators\MySQL;

use AMPortal\DataEngine\Models\Connection;

use Phalcon\Db\Adapter\Pdo\Mysql as AdapterPdoMySQL;
use PDO;

trait TraitAdapter {

	protected function _initAdapter() {
		$this->_defaultPort = 3306;
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

		return new AdapterPdoMySQL(array(
			'host'		=> $host,
			'username'	=> $c->username,
			'password'	=> $c->password,
			'dbname'	=> $base,
			'options'	=> array(
				PDO::ATTR_ERRMODE			=> PDO::ERRMODE_EXCEPTION,
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
			//	PDO::ATTR_CASE               => PDO::CASE_LOWER
			),
		));

	}


}