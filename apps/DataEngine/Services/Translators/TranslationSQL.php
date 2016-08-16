<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

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

	/**
	 * @var $query String
	 */
	protected $_query;
	protected $_statement;
	protected $_preparedFor;

	/**
	 * @var PdoAdapter    $_connection Internal connection to the SQL database
	 */
	protected $_adapter;
	protected $_connection;
	protected $_collection;
	protected $_placeholders;
	protected $_fields;
	protected $_filters;
	protected $_links;


	abstract protected function _createAdapter(Connection $c);



	public function __construct(\Phalcon\Di $di, Connection $c) {
		$this->_connection = $c;
		parent::__construct($di);
	}


	
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
	 *
	 * @param array $fields  An array of Fields objects
	 * @return string 
	 */
	protected function _sql_generate_select() {

		$s_select = '';
		$i_flds = 0;
		foreach ($this->_fields as $s_name=>$o_field) {

			$s_field = $this->_transform($o_field);

			// Add to select clause
			if ($i_flds > 0)
				$s_select .= ', ';
			$s_select .= $s_field;

			// Add the alias clause if needed (calculated field, renaming...)
			if (!empty($s_name) && $s_field != $s_name)
				$s_select .= ' AS '.$s_name;
			$i_flds++;
		}

		return 'SELECT '.$s_select;
	}

	/**
	 * 
	 */
	protected function _sql_generate_from() {
		
		$s_sqlFrom = '';
		$o_primaryTable = $this->_collection->getPlaceholderPrimary();
		$i_primaryTableId = $o_primaryTable->getId();

		// Check all placeholders
		foreach ($this->_placeholders as $i_phId=>$o_ph) {

			$s_path  = $o_ph->path;
			
			// Get the primary field table for FROM clause
			if ($i_phId == $i_primaryTableId) {

				// Prepend the from before the other parts
				$s_sqlFrom = ' FROM '.$s_path.' '.$s_sqlFrom;

				// And stop adding more
				$i_primaryTable = '';
			}
			// Non primary tables are joins
			else {
				// Append the join after the main table
				$s_sqlFrom .= $this->_sql_generate_join($o_primaryTable, $o_ph, 'LEFT');
			}
		}

		return $s_sqlFrom;
	}


	/**
	 * Generate a JOIN clause, using Link elements
	 *
	 * @return String
	 */
	protected function _sql_generate_join(Placeholder $phPrimary, Placeholder $ph, $joinType) {

		$s_join = '';
		$o_link = Link::findFirst(array(
			'idPlaceholderSrc = ?0 AND idPlaceholderDst = ?1',
			'bind' => array($phPrimary->getId(), $ph->getId())
		));

		if (!$o_link) {
			throw new \Exception('Unable to find a Link between src="'.$phPrimary->name.'" ('.$phPrimary->getId().') and dst="'.$ph->name.'" ('.$ph->getId().')');
		}

		switch ($joinType) {

			case 'LEFT':
			case 'INNER':
			case 'OUTER':

				$s_join .= $joinType.' JOIN '.$ph->path;
				if (!empty($ph->alias))
					$s_join .= ' AS '.$ph->alias;
				$s_join .= ' ON true';
				
				// Get all fields from clause
				foreach ($o_link->getFields() as $i_phSrc=>$i_phDst) {
					$o_fldSrc = Field::findFirst($i_phSrc);
					$o_fldDst = Field::findFirst($i_phDst);

					$s_join .= ' AND '.$o_fldSrc->getPlaceholder()->path.'.'.$o_fldSrc->path.' = '.$o_fldDst->getPlaceholder()->path.'.'.$o_fldDst->path.' ';
				}

				break;

			case 'RIGHT':
				throw new \Exception('We made the choice to avoid RIGHT JOINs. ');
				break;
		}

		return $s_join;
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
	protected function _sql_generate_having() {

	}

	/**
	 *
	 *
	 *
	 */
	protected function _sql_generate_limit() {

	}



	/**
	 * Transform the queried column if required in field definition
	 * 
	 * @return string
	 */
	protected function _transform(Field $field) {
		
		$s_fld = $field->getPlaceholder()->path.'.'.$field->path;

		if ($field->transformation)
			$s_fld = str_replace($field->transformation, "%%FIELD%%", $s_fld);

		return $s_fld;
	}


	// ////////////////////////////////////////////////////////////////////////
	//
	// Public
	// 
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

		return $this->_getAdapter($c);
	}

	// ////////////////////////////////////////////////////
	// 
	// Export stubs
	
	/**
	 * Export data from database
	 * @param Collection $coll Collection to be translated to SQL query
	 *
	 */
	public function prepareExport(Collection $coll) {

		// Check for mutex
		if (!empty($this->_preparedFor))
			throw new \Exception("This instance is already prepared for ".$this->_preparedFor);


		$this->_collection = $coll;

		//
		// Process each field of the collection
		//
		foreach ($coll->getFields() as $o_fld) {

			// Save the field
			$this->_fields[$o_fld->name] = $o_fld;

			// Find and save the placeholder
			$o_ph = $o_fld->getPlaceholder();
			$i_phId = $o_ph->getId();

			if (!isset($this->_placeholders[$i_phId]))
				$this->_placeholders[$i_phId] = $o_ph;
		}

		//
		// We got our data, now let's generate this SQL query !
		// TODO: Check the query builder :
		//   https://docs.phalconphp.com/en/latest/api/Phalcon_Mvc_Model_Query_Builder.html
		//
		$this->_query  = 
				  $this->_sql_generate_select()	// 1 - Select fields
			.' '. $this->_sql_generate_from()	// 2 - From tables
			.' '. $this->_sql_generate_where()	// 3 - Where Filtering
			.' '. $this->_sql_generate_group()	// 4 - Grouping
			.' '. $this->_sql_generate_having()	// 5 - Having
			.' '. $this->_sql_generate_limit()	// 6 - Limits
			;


		//
		// Finally, our statement
		//
		$this->_statement = $this->_getAdapter()->query($this->_query);
		$this->_statement->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

		$this->_preparedFor = 'export';

		return $this->_statement;
	}

	/**
	 * Execute the prepared export
	 * 
	 * @return Array : row of data
	 */
	public function export() {
		if (empty($this->_statement))
			throw new \Exception("The export must be prepared before !");

		// Return a row
		return $this->_statement->fetch();
	}




	// ////////////////////////////////////////////////////
	// 
	// Import stubs
	
	/**
	 * 
	 *
	 */
	public function setImportStrategy($strat) {
		switch ($strat) {
			case 'copy':

				break;
			case 'view':

				break;

			default:
				throw new \Exception('Unknown import strategy "'.$strat.'"');
				break;
		}
	}

	/**
	 * Import data into database
	 *
	 */
	public function prepareImport(Collection $coll) {

		// Check for mutex
		if (!empty($this->_preparedFor))
			throw new \Exception("This instance is already prepared for ".$this->_preparedFor);


		// Save the collection for sub use
		$this->_collection = $coll;

		// 
		// Parsing : Get all elements from collection
		//
		foreach ($coll->getFields() as $i_fldId=>$o_fld) {
			// Save the field
			$this->_fields[$o_fld->name] = $o_fld;

			// Find and save the placeholder
			$o_ph = $o_fld->getPlaceholder();
			$i_phId = $o_ph->getId();

			// Check for only one destination placeholder
			if ($coll->getPlaceholderPrimaryId() != $i_phId)
				throw new \Exception('Can only import into primary placeholder : '.$coll->getPlaceholderPrimary()->name.'" != "'.$o_ph->name.'"');

			// Add the field to the list
			$this->_fields[$o_fld->name] = $o_fld;
		}



		$a_cols = array();
		foreach ($this->_fields as $s_fldName=>$o_fld) {
			$a_cols[] = $o_fld->name;
		}

		// INSERT INTO $table (f1, f2, f3) VALUES (:f1, :f2, :f3)
		$this->_query = 'INSERT INTO '.$this->_collection->getPlaceholderPrimary()->path
				.' ('. implode(',',$a_cols) .') VALUES (:'.implode(', :', $a_cols).')';

		// ON DUPLICATE KEY UPDATE
		$this->_query .= ' ON DUPLICATE KEY UPDATE ';
		$i_updCnt = 0;
		foreach ($a_cols as $s_col) {
			if ($i_updCnt > 0)
				$this->_query .= ', ';

			$this->_query .= $s_col.' = VALUES('.$s_col.')';
			$i_updCnt++;
		}



		// Prepare the insert
		$this->_statement = $this->_getAdapter()->prepare($this->_query);


		$this->_preparedFor = 'import';

		return $this->_statement;
	}

	/**
	 *
	 *
	 */
	public function import($data) {

		try {

			$this->_adapter->executePrepared($this->_statement, $data, array());

		} catch (\PDOException $e) {
			echo '<strong>This dataset is invalid</strong> : <u>', $e->getMessage(), '</u>';
			echo '<pre>', print_r($data, true), '</pre>';
			return false;
		} catch (\Exception $e) {
			echo '<strong>An unhandled error occured</strong> : <u>', $e->getMessage(), '</u>';
			echo '<pre>', print_r($data, true), '</pre>';
			return false;
		}

		return true;
	}

	/**
	 * Multiple arrays at a time
	 *
	 */
	public function importMultiple($datas) {

	}
}