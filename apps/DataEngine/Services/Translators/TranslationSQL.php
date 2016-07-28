<?php

namespace AMPortal\DataEngine\Services\Translators;

use AMPortal\DataEngine\Services\BaseService;
use AMPortal\DataEngine\Services\InterfaceTranslator;

use AMPortal\DataEngine\Models\Collection;
use AMPortal\DataEngine\Models\Connection;
use AMPortal\DataEngine\Models\Placeholder;
use AMPortal\DataEngine\Models\Field;
use AMPortal\DataEngine\Models\Filter;
use AMPortal\DataEngine\Models\Link;


use Phalcon\Db\Adapter\Pdo as PdoAdapter;
use Phalcon\Di;

/**

The translator is responsible to 
 - popupate collections (import data to php) 
 - store collections (export to sql)
in the SQL langage of the database

According to the strategy, it can also create views to avoid data duplication

*/

abstract class TranslationSQL extends BaseService implements InterfaceTranslator {

	protected $_defaultPort = 0;

	/**
	 * @var $query String
	 */
	protected $_query;

	/**
	 * 
	 */
	protected $_fields;
	protected $_tables;
	protected $_filters;
	protected $_clauses;
	protected $_links;

	/**
	 * @var PdoAdapter    $_connection Internal connection to the SQL database
	 */
	protected $_adapter;
	protected $_connection;


	abstract protected function _createAdapter(Connection $c);

	
	/**
	 *
	 */
	protected function _getAdapter(Connection $c = null) {
		if (!$this->_adapter) {
			// Try to use current connection if not provided
			if (!$c)
				$c = $this->_connection;

			$this->_adapter = $this->_createAdapter($c);
		}

		return $this->_adapter;
	}


	/**
	 * Escape a string for usage in SQL query
	 * @param  String  $txt
	 * @return String  Escaped $txt
	 */
	protected function _esc($txt) {
		return $this->_getAdapter()->escapeString($txt);
	}


	/**
	 * Generate the fields part of the query
	 * @param array $fields  An array of Fields objects
	 * 
	 */
	protected function _sql_generate_fields() {
		$s_select = '';
		$i_flds = 0;
		foreach ($this->_fields as $s_alias=>$s_field) {
			if ($i_flds > 0)
				$s_select .= ', ';
			$s_select .= $this->_esc($s_field).' AS '.$this->_esc($s_alias);
			$i_flds++;
		}

		return $s_select;
	}

	/**
	 *
	 */
	protected function _sql_generate_tables() {
		
		$i_tbls = 0;
		foreach ($this->_tables as $s_alias=>$s_name) {
			if ($i_tbls == 0) {
				$this->_query .= ' FROM '.$this->_esc($s_name).' AS '.$this->_esc($s_alias);
			}
			else {
				$this->_query .= $this->_sql_generate_join($s_name, $s_alias, 'LEFT');
			}

			$i_tbls++;
		}

	}

	/**
	 *
	 */
	protected function _sql_generate_where() {
		
	}

	/**
	 *
	 *
	 */
	protected function _sql_generate_group() {
		
	}
	
	/**
	 *
	 *
	 */
	protected function _transform(Field $field) {

	}


	// ////////////////////////////////////////////////////////////////////////
	// Public stubs
	// ////////////////////////////////////////////////////////////////////////
	

	/**
	 * @param  Connection $c 
	 * @return String     The UUID of the connection
	 */
	public function getConnectionUid(Connection $c) {
		return $c->getUid();
	}

	/**
	 * Test the connection to the database
	 *
	 */
	public function testConnection(Connection $c) {
		return $this->_getAdapter($c)->connect();
	}

	// ////////////////////////////////////////////////////
	// Import / Export stubs
	
	/**
	 * Export data from database
	 * @param Collection $coll Collection to be translated to SQL query
	 *
	 */
	public function prepareExport(Collection $coll) {

		// Parse the collection for placeholders
		foreach ($coll->getPlaceholders() as $s_ph=>$o_ph) {

			// Foreach placeholders
			$this->_tables[$s_ph];
		}

		// We got our data, now let's generate this SQL query !

		
		$this->_query  = 
				  $this->_sql_generate_fields()	// 1 - Select fields
			.' '. $this->_sql_generate_tables()	// 2 - From tables
			.' '. $this->_sql_generate_where()	// 3 - Where Filtering
			.' '. $this->_sql_generate_group()	// 4 - Grouping
			.' '. $this->_sql_generate_having()	// 5 - Having
			.' '. $this->_sql_generate_limit()	// 6 - Limits
			;

	}

	/**
	 * Execute the prepared export
	 * 
	 * @return &Array : row of data
	 */
	public function & export() {
		if (empty($this->s_query))
			throw new Exception("The export must be prepared before !");

		return $this->_connection->query();
	}


	/**
	 * Import data into database
	 *
	 */
	public function prepareImport(Collection $coll) {

		// Get all elements from 
		

	}


	public function import($data) {

	}
}