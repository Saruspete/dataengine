<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\DataEngine\Services\Translators;

use AMPortal\DataEngine\Services\BaseService;
use AMPortal\DataEngine\Services\InterfaceDiscover;
use AMPortal\DataEngine\Models\Connection;
use AMPortal\DataEngine\Models\Placeholder;
use AMPortal\DataEngine\Models\Field;
use AMPortal\DataEngine\Models\Link;

use Phalcon\Db\Adapter\Pdo as PdoAdapter;
use Phalcon\Db\Column;
use Phalcon\Validation\Validator;
use Phalcon\Validation\Validator\StringLength as ValidatorStringLength;
use Phalcon\Validation\Validator\Between as ValidatorBetween;

abstract class DiscoverySQL extends BaseService implements InterfaceDiscover {


	/**
	 * @var PdoAdapter    $_connection Internal connection to the SQL database
	 */
	protected $_adapter;

	protected $_placeholders = array();
	protected $_fields = array();


	/**
	 * SQL-specific stubs
	 */
	abstract protected function _listDatabases(PdoAdapter $db);
	abstract protected function _listTables(PdoAdapter $db, $dbname);
	abstract protected function _listColumns(PdoAdapter $db, $dbname, $tablename);
	abstract protected function _createAdapter(Connection $c);


	protected function _getAdapter(Connection $c = NULL) {
		if (!$this->_adapter) {
			// Try to use current connection if not provided
			if (!$c)
				$c = $this->_connection;

			$this->_adapter = $this->_createAdapter($c);
		}

		return $this->_adapter;
	}


	/**
	 * @param  Connection $c 
	 * @return String     The UUID of the connection
	 */
	public function getConnectionUid(Connection $c) {
		return $c->getUid();
	}


	public function testConnection(Connection $c) {
		// TODO: Why ->connect() returns null ?
		return $this->_getAdapter($c);
	}


	/**
	 * 
	 */
	public function discoverStructure(Connection $conn, $shallUpdate = false) {

		$db = $this->_getAdapter($conn);
		//$s_connUid = $this->getConnectionUid($conn);
		$i_connId = $conn->getId();

		$a_placeholders = array();
		$a_bases = array();

		if ($conn->resource) {
			$a_bases[] = $conn->resource;
		}
		else {
			$a_bases = $this->_listDatabases($db);
		}

		echo "<h2>Discovering structure</h2>";

		// List databases
		foreach ($a_bases as $s_base) {
			echo "<h3>$s_base</h3>";

			// List tables
			foreach ($this->_listTables($db, $s_base) as $s_table) {

				$s_phpath = $s_base.'.'.$s_table;
				$s_phname = $s_base.' - '.$s_table;

//				echo '<h2>', $s_phname, ' (', $s_phpath,')</h2>';

				// Try to get an existing placeholder
				$o_ph = Placeholder::findFirst(array(
					"path='$s_phpath'",
					"order"	=> "id DESC",
				));

				// No object was found, create a new one
				if (!$o_ph) {
					$o_ph = new Placeholder();

					// Set base objects
					$o_ph->path = $s_phpath;
					$o_ph->name = $s_phname;
					$o_ph->alias = '';
					$o_ph->type = 'origin';
					$o_ph->idConnection = $i_connId;

				}

				// Get the number of rows in the placeholder
				/*
				$o_countRes = $this->_getAdapter($conn)->query('SELECT COUNT(*) FROM '.$s_phpath);
				$o_countRes->setFetchMode(\Phalcon\Db::FETCH_NUM);
				$o_ph->rowsCount = $o_countRes->fetch()[0];
				*/
				
				$o_ph->rowsCount = $this->_getAdapter($conn)->fetchColumn('SELECT COUNT(*) FROM '.$s_phpath);



				// And save the new values
				if (!$o_ph->save()) {
					$msg = 'Error during save of Placeholder "'. $s_phpath.'"';
					foreach ($o_ph->getMessages() as $message) {
						$msg .= ' : '.$message;
					}
					throw new \Exception($msg);
				}



				$i_idplaceholder = $o_ph->getId();
				

				// Get the indexes for this table and list them per-column
				$a_indexes = array();
				foreach ($this->_listIndexes($db, $s_base, $s_table) as $o_index) {

					foreach ($o_index->getColumns() as $s_col) { 
						$a_indexes[$s_col] = $o_index;
//						echo "=== Index for $s_col : ", var_dump($o_index), "==";
					}
//					echo "<pre>", print_r($o_index, true), "</pre>";
				}



				// List fields
				foreach ($this->_listColumns($db, $s_base, $s_table) as $o_column) { 
					
//					echo '<pre>', var_dump($o_column), '</pre>';
					$s_path = $o_column->getName();

					// Find an exsiting record for this field
					$o_fld = Field::findFirst(array(
						'idPlaceholder="'.$i_idplaceholder.'" 
							AND path="'.$s_path.'"
							AND source="original" ',
					));


					// If the field was not found
					if (!$o_fld) {

						$o_fld = new Field();

						// Set base attributes
						$o_fld->setPlaceholder($o_ph);
						$o_fld->path = $s_path;
						$o_fld->name = $s_path;			// Default: same as path
						$o_fld->format = 'string';		// Default: string
						$o_fld->source = 'original';	// Default: golden source
						$o_fld->attributes = '';		// Default: No attribute

						// Add the index type if any
						if (!empty($a_indexes[$o_fld->path])) {
							$o_idx = $a_indexes[$o_fld->path];
							$o_fld->attributes = $this->_getIndexType($o_idx);
						}



						$i_size = $o_column->getSize();
						// Parse extended attributes
						switch($o_column->getType()) {
				
							// Taken from Phalcon\Db\Column

							// Numbers
							case Column::TYPE_INTEGER:	// 0
								//$o_fld->addValidation(new ValidatorBetween());
								$o_fld->format = 'INT('.$i_size.')';
								break;
							case Column::TYPE_DECIMAL:	// 3
								break;
							case Column::TYPE_FLOAT:	// 7
								break;
							case Column::TYPE_BOOLEAN:	// 8
								break;
							case Column::TYPE_DOUBLE:	// 9
								break;
							case Column::TYPE_BIGINTEGER:	// 14
								break;

							// Date 
							case Column::TYPE_DATE:		// 1
								$o_fld->type = 'date';
								break;
							case Column::TYPE_DATETIME:	// 4
								$o_fld->type = 'datetime';
								break;
							case Column::TYPE_TIMESTAMP:	// 17
								break;

							// String
							case Column::TYPE_TEXT:		// 6
							case Column::TYPE_VARCHAR:	// 2
							case Column::TYPE_CHAR:		// 5
								//$o_fld->addValidation(new ValidatorStringLength(0, $i_size));
								$o_fld->type = 'string';
								break;

							//  Binary fields
							case Column::TYPE_TINYBLOB:	// 10
								break;
							case Column::TYPE_BLOB:		// 11
								break;
							case Column::TYPE_MEDIUMBLOB:	// 12
								break;
							case Column::TYPE_LONGBLOB:		// 13
								break;

							// JSON values
							case Column::TYPE_JSON:		// 15
								break;
							case Column::TYPE_JSONB:	// 16
								break;
							
							// Unhandled type, must update code
							default:
								break;
						}





						// Try to save it
						if (!$o_fld->save()) {
							$msg = 'Error during save of Field '.$o_column->getName().': ';
							foreach ($o_fld->getMessages() as $message) {
								$msg .= $message."\n";
							}
							throw new \Exception($msg);
						}

					}

					// Add parent link
					$o_ph->addField($o_fld);

				}

				// Every stuff here should be unique
				$this->_placeholders[$s_phpath] = $o_ph;

			}



			// List views
			foreach ($this->_listViews($db, $s_base) as $s_view) {
				echo "<h1>View : ",$s_view,"</h1>";
			}



		}


		// Return the placeholders
		return array(
			'placeholders'	=> array_keys($this->_placeholders),
			'fields'		=> array_keys($this->_fields),
		);
	}


	/**
	 * 
	 *
	 */
	public function discoverRelations(Connection $conn) {

		$db = $this->_getAdapter($conn);
		//$s_connUid = $this->getConnectionUid($conn);
		$i_connId = $conn->id;

		$a_placeholders = array();
		$a_bases = array();

		if ($conn->resource) {
			$a_bases[] = $conn->resource;
		}
		else {
			$a_bases = $this->_listDatabases($db);
		}

		echo "<h2>Discovering relations</h2>";

		// List databases
		foreach ($a_bases as $s_base) {
			echo "<h3>$s_base</h3>";


			// Get the foreign keys
			foreach ($this->_placeholders as $s_phpath=>$o_ph) {
				list($s_baseName, $s_tableName) = explode('.', $s_phpath);

				// Skip records for other databases in current connection
				if ($s_baseName != $s_base)
					continue;

				echo "=== References for $s_phpath ===<br />";

				
				// Create a link object from these references
				foreach ($this->_listReferences($db, $s_base, $s_tableName) as $o_ref) {

					// Reference names
					$s_refSchema = $o_ref->getReferencedSchema();
					$s_refTable  = $o_ref->getReferencedTable();
					$s_locSchema = $s_base;
					$s_locTable  = $s_tableName;

					$a_refFields = $o_ref->getReferencedColumns();
					$a_locFields = $o_ref->getColumns();

					if (count($a_refFields) > 6)
						throw new \Exception('Unable to manage reference "'.$o_ref->getName().'" with '.count($a_refFields).' (6 max)');


					for ($i=0; $i<count($a_refFields); $i++) {
						$a_fields = array($a_locFields[$i]	=> $a_refFields[$i]);
					}

					// Placeholder objects
					$o_refPlaceholder = $this->_placeholders[$s_refSchema.".".$s_refTable];
					$o_locPlaceholder = $this->_placeholders[$s_phpath];

					if (!$o_refPlaceholder)
						throw new \Exception("Unable to get referenced placeholder object");
					if (!$o_locPlaceholder)
						throw new \Exception("Unable to get local placeholder object");

//echo print_r(array_keys($this->_placeholders));
//echo var_dump($o_refPlaceholder);


					// Build the clause to find an existing link
					$s_searchClause = 
						'idPlaceholderSrc = "'.$o_locPlaceholder->getId().'" AND 
						 idPlaceholderDst = "'.$o_refPlaceholder->getId().'" ';

					$i = 0;
					foreach ($a_fields as $s_loc=>$s_ref) {
						$o_locField = $o_locPlaceholder->getField($s_loc);
						$o_refField = $o_refPlaceholder->getField($s_ref);
						
						$s_searchClause .= ' AND 
							idFieldSrc'.$i.' = "'.$o_locField->getId().'" AND
							idFieldDst'.$i.' = "'.$o_refField->getId().'"';
						$i++;
					}

					// Search an existing link
					$o_lnk = Link::findFirst(array(
						$s_searchClause,
					));

					// If nothing found, create one
					if (!$o_lnk) {
						$o_lnk = new Link();
						$o_lnk->name = $o_ref->getName();
						$o_lnk->idPlaceholderSrc = $o_locPlaceholder->getId();
						$o_lnk->idPlaceholderDst = $o_refPlaceholder->getId();

						$i = 0;
						foreach ($a_fields as $s_loc=>$s_ref) {
							$o_locField = $o_locPlaceholder->getField($s_loc);
							$o_refField = $o_refPlaceholder->getField($s_ref);

							$o_lnk->{'idFieldSrc'.$i} = $o_locField->getId();
							$o_lnk->{'idFieldDst'.$i} = $o_refField->getId();
							$i++;
						}

						// Try to save it
						if (!$o_lnk->save()) {
							$msg = 'Error during save of Link '.$o_lnk->name.': ';
							foreach ($o_lnk->getMessages() as $message) {
								$msg .= $message."\n";
							}
							throw new \Exception($msg);
						}

					}
				}

			}
		}
	}




}