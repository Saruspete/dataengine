<?php


namespace AMPortal\DataEngine\Services\Translators\MSSQL;

use Phalcon\Db;
use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\AdapterInterface;
use Phalcon\Db\Adapter\Pdo as PdoAdapter;

/**
 * AMPortal\DataEngine\Services\Translators\MSSQL;
 *
 * Specific functions for the MSSQL database system
 *
 *<code>
 * use AMPortal\DataEngine\Services\Translators\MSSQL\Driver;
 *
 * $config = [
 *   'host'     => 'localhost',
 *   'dbname'   => 'blog',
 *   'port'     => 3306,
 *   'username' => 'sigma',
 *   'password' => 'secret'
 * ];
 *
 * $connection = new Mysql($config);
 *</code>
 */

// Wrapper function from Zephir
function memstr($mystring, $findme) {
	return (strpos($mystring, $findme) !== false);
}
function globals_get($feature) {
	return false;
}

require __DIR__.'/MSSQLDialect.php';


class MSSQLDriver extends PdoAdapter implements AdapterInterface {

	protected $_type = "dblib";
	protected $_dialectType = "mssql";


	public function escapeIdentifier($identifier) {
		$domain = '';
		$name = '';

		if (is_array($identifier)) {
			$domain = $identifier[0];
			$name = $identifier[1];

			if (globals_get("db.escape_identifiers")) {
				return "`" . $domain . "`.`" . $name . "`";
			}
			return $domain . "." . $name;
		}

		if (globals_get("db.escape_identifiers")) {
			return "`" . $identifier . "`";
		}

		return $identifier;
	}


	public function describeColumns($table, $schema = null) {

		$oldColumn = null;
		$sizePattern = "#\\(([0-9]+)(?:,\\s*([0-9]+))*\\)#";

		$columns = [];

		/**
		 * Get the SQL to describe a table
		 * We're using FETCH_NUM to fetch the columns
		 * Get the describe
		 * Field Indexes: 0:name, 1:type, 2:not null, 3:key, 4:default, 5:extra
		 */
		foreach($this->fetchAll($this->_dialect->describeColumns($table, $schema), Db::FETCH_NUM) as $field ) {

			/**
			 * By default the bind types is two
			 */
			$definition = ["bindType" => Column::BIND_PARAM_STR];

			/**
			 * By checking every column type we convert it to a Phalcon\Db\Column
			 */
			$columnType = $field[1];

			while (1) {

				/**
				 * Enum are treated as char
				 */
				if (memstr($columnType, "enum")) {
					$definition["type"] = Column::TYPE_CHAR;
					break;
				}

				/**
				 * Smallint/Bigint/Integers/Int are int
				 */
				if (memstr($columnType, "bigint")) {
					$definition["type"] = Column::TYPE_BIGINTEGER;
					$definition["isNumeric"] = true;
					$definition["bindType"] = Column::BIND_PARAM_INT;
					break;
				}

				/**
				 * Smallint/Bigint/Integers/Int are int
				 */
				if (memstr($columnType, "int")) {
					$definition["type"] = Column::TYPE_INTEGER;
					$definition["isNumeric"] = true;
					$definition["bindType"] = Column::BIND_PARAM_INT;
					break;
				}

				/**
				 * Varchar are varchars
				 */
				if (memstr($columnType, "varchar")) {
					$definition["type"] = Column::TYPE_VARCHAR;
					break;
				}

				/**
				 * Special type for datetime
				 */
				if (memstr($columnType, "datetime")) {
					$definition["type"] = Column::TYPE_DATETIME;
					break;
				}

				/**
				 * Chars are chars
				 */
				if (memstr($columnType, "char")) {
					$definition["type"] = Column::TYPE_CHAR;
					break;
				}

				/**
				 * Date are dates
				 */
				if (memstr($columnType, "date")) {
					$definition["type"] = Column::TYPE_DATE;
					break;
				}

				/**
				 * Timestamp are dates
				 */
				if (memstr($columnType, "timestamp")) {
					$definition["type"] = Column::TYPE_TIMESTAMP;
					break;
				}

				/**
				 * Text are varchars
				 */
				if (memstr($columnType, "text")) {
					$definition["type"] = Column::TYPE_TEXT;
					break;
				}

				/**
				 * Decimals are floats
				 */
				if (memstr($columnType, "decimal")){
					$definition["type"] = Column::TYPE_DECIMAL;
					$definition["isNumeric"] = true;
					$definition["bindType"] = Column::BIND_PARAM_DECIMAL;
					break;
				}

				/**
				 * Doubles
				 */
				if (memstr($columnType, "double")){
					$definition["type"] = Column::TYPE_DOUBLE;
					$definition["isNumeric"] = true;
					$definition["bindType"] = Column::BIND_PARAM_DECIMAL;
					break;
				}

				/**
				 * Float/Smallfloats/Decimals are float
				 */
				if (memstr($columnType, "float")) {
					$definition["type"] = Column::TYPE_FLOAT;
					$definition["isNumeric"] = true;
					$definition["bindType"] = Column::BIND_PARAM_DECIMAL;
					break;
				}

				/**
				 * Boolean
				 */
				if (memstr($columnType, "bit")) {
					$definition["type"] = Column::TYPE_BOOLEAN;
					$definition["bindType"] = Column::BIND_PARAM_BOOL;
					break;
				}

				/**
				 * Tinyblob
				 */
				if (memstr($columnType, "tinyblob")) {
					$definition["type"] = Column::TYPE_TINYBLOB;
					$definition["bindType"] = Column::BIND_PARAM_BOOL;
					break;
				}

				/**
				 * Mediumblob
				 */
				if (memstr($columnType, "mediumblob")) {
					$definition["type"] = Column::TYPE_MEDIUMBLOB;
					break;
				}

				/**
				 * Longblob
				 */
				if (memstr($columnType, "longblob")) {
					$definition["type"] = Column::TYPE_LONGBLOB;
					break;
				}

				/**
				 * Blob
				 */
				if (memstr($columnType, "blob")) {
					$definition["type"] = Column::TYPE_BLOB;
					break;
				}

				/**
				 * By default is string
				 */
				$definition["type"] = Column::TYPE_VARCHAR;
				break;
			}

			/**
			 * If the column type has a parentheses we try to get the column size from it
			 */
			if (memstr($columnType, "(")) {
				$matches = null;
				if (preg_match($sizePattern, $columnType, $matches)) {
					if (isset($matches[1])) {
						$definition["size"] = (int) $matches[1];
					}
					if (isset($matches[2])) {
						$definition["scale"] = (int) $matches[2];
					}
				}
			}

			/**
			 * Check if the column is unsigned, only MySQL support this
			 */
			if (memstr($columnType, "unsigned")) {
				$definition["unsigned"] = true;
			}

			/**
			 * Positions
			 */
			if ($oldColumn == null) {
				$definition["first"] = true;
			} else {
				$definition["after"] = oldColumn;
			}

			/**
			 * Check if the field is primary key
			 */
			if ($field[3] == "PRI") {
				$definition["primary"] = true;
			}

			/**
			 * Check if the column allows null values
			 */
			if ($field[2] == "NO") {
				$definition["notNull"] = true;
			}

			/**
			 * Check if the column is auto increment
			 */
			if ($field[5] == "auto_increment") {
				$definition["autoIncrement"] = true;
			}

			/**
			 * Check if the column is default values
			 */
			if (!is_null($field[4])) {
				$definition["default"] = $field[4];
			}

			/**
			 * Every route is stored as a Phalcon\Db\Column
			 */
			$columnName = $field[0];
			$columns[] = new Column($columnName, $definition);
			$oldColumn = $columnName;
		}

		return $columns;
	}

	/**
	 * Lists table indexes
	 *
	 * <code>
	 *   print_r($connection->describeIndexes('robots_parts'));
	 * </code>
	 *
	 * @param  string table
	 * @param  string schema
	 * @return \Phalcon\Db\IndexInterface[]
	 */
	
	public function describeIndexes($table, $schema = null) {
		
		$indexes = [];
		foreach ($this->fetchAll($this->_dialect->describeIndexes($table, $schema), Db::FETCH_ASSOC) as $index) {
			$keyName = $index["Key_name"];
			$indexType = $index["Index_type"];

			if (!isset($indexes[$keyName])) {
				$indexes[$keyName] = [];
			}

			if (!isset($indexes[$keyName]["columns"])) {
				$columns = [];
			} else {
				$columns = $indexes[$keyName]["columns"];
			}

			$columns[] = $index["Column_name"];
			$indexes[$keyName]["columns"] = $columns;

			if (keyName == "PRIMARY") {
				$indexes[$keyName]["type"] = "PRIMARY";
			} elseif ($indexType == "FULLTEXT") {
				$indexes[$keyName]["type"] = "FULLTEXT";
			} elseif ($index["Non_unique"] == 0) {
				$indexes[$keyName]["type"] = "UNIQUE";
			} else {
				$indexes[$keyName]["type"] = null;
			}
		}

		$indexObjects = [];
		foreach ($indexes as $name=>$index)  {
			$indexObjects[$name] = new Index($name, $index["columns"], $index["type"]);
		}

		return $indexObjects;
	}
	

	/**
	 * Lists table references
	 *
	 *<code>
	 * print_r($connection->describeReferences('robots_parts'));
	 *</code>
	 */
	
	public function describeReferences($table, $schema = null) {

		$references = [];

		foreach ($this->fetchAll($this->_dialect->describeReferences($table, $schema),Db::FETCH_NUM) as $reference) {

			$constraintName = $reference[2];
			if (!isset($references[$constraintName])) {
				$referencedSchema  = $reference[3];
				$referencedTable   = $reference[4];
				$referenceUpdate   = $reference[6];
				$referenceDelete   = $reference[7];
				$columns           = [];
				$referencedColumns = [];

			} else {
				$referencedSchema  = $references[$constraintName]["referencedSchema"];
				$referencedTable   = $references[$constraintName]["referencedTable"];
				$columns           = $references[$constraintName]["columns"];
				$referencedColumns = $references[$constraintName]["referencedColumns"];
				$referenceUpdate   = $references[$constraintName]["onUpdate"];
				$referenceDelete   = $references[$constraintName]["onDelete"];
			}

			$columns[] = $reference[1];
			$referencedColumns[] = $reference[5];

			$references[$constraintName] = [
				"referencedSchema"  => $referencedSchema,
				"referencedTable"   => $referencedTable,
				"columns"           => $columns,
				"referencedColumns" => $referencedColumns,
				"onUpdate"          => $referenceUpdate,
				"onDelete"          => $referenceDelete
			];
		}

		$referenceObjects = [];
		foreach ($references as $name=>$arrayReference)  {
			$referenceObjects[$name] = new Reference($name, [
				"referencedSchema"  => $arrayReference["referencedSchema"],
				"referencedTable"   => $arrayReference["referencedTable"],
				"columns"           => $arrayReference["columns"],
				"referencedColumns" => $arrayReference["referencedColumns"],
				"onUpdate"          => $arrayReference["onUpdate"],
				"onDelete"          => $arrayReference["onDelete"]
			]);
		}

		return $referenceObjects;
	}
	

}