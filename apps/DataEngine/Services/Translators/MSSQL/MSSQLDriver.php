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


// Include the dialect from plain PHP
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
		
		$columns = [];

		/**
		 * Get the SQL to describe a table
		 * We're using FETCH_ASSOC to fetch the columns
		 * Get the describe
		 * Field Indexes: 0:name, 1:type, 2:not null, 3:key, 4:default, 5:extra
		 */
		foreach($this->fetchAll($this->_dialect->describeColumns($table, $schema), Db::FETCH_NUM) as $field ) {

			/**
			 * By default the bind types is two
			 */
			$definition = ["bindType" => Column::BIND_PARAM_STR];

			
			switch ($field['DATA_TYPE']) {

				// ENUM
				case 'enum':
					$definition["type"] = Column::TYPE_CHAR;
					break;
				
				
				// Strings
				case 'varchar':
				case 'nvarchar':
					$definition["type"] = Column::TYPE_VARCHAR;
					break;

				case 'char':
				case 'nchar':
					$definition["type"] = Column::TYPE_CHAR;
					break;
				
				case 'text':
				case 'ntext':
				case 'xml':
					$definition["type"] = Column::TYPE_TEXT;
					break;
				
				// Date and Time
				case 'datetime':
				case 'datetime2':
				case 'smalldatetime':
				case 'datetimeoffset':
					$definition["type"] = Column::TYPE_DATETIME;
					break;
				
				case 'date':
					$definition["type"] = Column::TYPE_DATE;
					break;
				
				case 'timestamp':
				case 'rowversion':
					$definition["type"] = Column::TYPE_TIMESTAMP;
					break;
				
				// Integers
				case 'bigint':
					$definition["type"] = Column::TYPE_BIGINTEGER;
				case 'smallint':
				case 'int':
					if (!isset($definition['type']))
						$definition["type"] = Column::TYPE_INTEGER;
					
					$definition["isNumeric"] = true;
					$definition["bindType"] = Column::BIND_PARAM_INT;
					break;
				
				// Decimal values
				case "decimal":
				case "numeric":
				case 'money':
				case 'smallmoney':
					$definition["type"] = Column::TYPE_DECIMAL;
					$definition["isNumeric"] = true;
					$definition["bindType"] = Column::BIND_PARAM_DECIMAL;
					break;

				case "double":
					$definition["type"] = Column::TYPE_DOUBLE;
					$definition["isNumeric"] = true;
					$definition["bindType"] = Column::BIND_PARAM_DECIMAL;
					break;

				case "real":
				case "float":
					$definition["type"] = Column::TYPE_FLOAT;
					$definition["isNumeric"] = true;
					$definition["bindType"] = Column::BIND_PARAM_DECIMAL;
					break;
				

				case "bit":
					$definition["type"] = Column::TYPE_BOOLEAN;
					$definition["bindType"] = Column::BIND_PARAM_BOOL;
					break;
				
				// Binary fields
				case 'binary':
				case 'varbinary':
				case "tinyblob":
					$definition["type"] = Column::TYPE_TINYBLOB;
					$definition["bindType"] = Column::BIND_PARAM_BOOL;
					break;
				
				case "mediumblob":
					$definition["type"] = Column::TYPE_MEDIUMBLOB;
					break;

				case "longblob":
					$definition["type"] = Column::TYPE_LONGBLOB;
					break;

				case 'image':
				case "blob":
					$definition["type"] = Column::TYPE_BLOB;
					break;
				

				default:
					$definition["type"] = Column::TYPE_VARCHAR;
					break;
			}


			
			$definition["size"] = (int) $field['CHARACTER_MAXIMUM_LENGTH'];
			//$definition["scale"] = (int) $matches[2];
			

			/**
			 * Positions
			 */
			if ($oldColumn == null) {
				$definition["first"] = true;
			} else {
				$definition["after"] = $oldColumn;
			}

			/**
			 * Check if the field is primary key
			 */
//			if ($field[3] == "PRI") {
//				$definition["primary"] = true;
//			}

			/**
			 * Check if the column allows null values
			 */
//			if ($field[2] == "NO") {
//				$definition["notNull"] = true;
//			}

			/**
			 * Check if the column is auto increment
			 */
//			if ($field[5] == "auto_increment") {
//				$definition["autoIncrement"] = true;
//			}

			/**
			 * Check if the column is default values
			 */
			if (!is_null($field['COLUMN_DEFAULT'])) {
				$definition["default"] = $field[4];
			}

			/**
			 * Every route is stored as a Phalcon\Db\Column
			 */
			$columnName = $field['COLUMN_NAME'];
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
			$keyName = $index["IndexName"];
			$indexType = $index["IndexType"];

			if (!isset($indexes[$keyName])) {
				$indexes[$keyName] = [];
			}

			if (!isset($indexes[$keyName]["columns"])) {
				$columns = [];
			} else {
				$columns = $indexes[$keyName]["columns"];
			}

			$columns[] = $index["ColumnName"];
			$indexes[$keyName]["columns"] = $columns;

			if ($keyName == "PRIMARY") {
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