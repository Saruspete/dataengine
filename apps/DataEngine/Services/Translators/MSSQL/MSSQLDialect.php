<?php


namespace Phalcon\Db\Dialect;

use Phalcon\Db\Dialect;
use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\ColumnInterface;
use Phalcon\Db\ReferenceInterface;
use Phalcon\Db\DialectInterface;

/**
 * Phalcon\Db\Dialect\Mysql
 *
 * Generates database specific SQL for the MySQL RDBMS
 */
class MSSQL extends Dialect
{

	protected $_escapeChar = "`";

	/**
	 * Gets the column name in MySQL
	 */
	public function getColumnDefinition(ColumnInterface $column) {
		
		$columnSql = "";

		$type = $column->getType();
		if (is_string(type)) {
			$columnSql .= $type;
			$type = $column->getTypeReference();
		}

		switch ($type) {

			case Column::TYPE_INTEGER:
				if (empty ($columnSql)) {
					$columnSql .= "INT";
				}
				$columnSql .= "(" . $column->getSize() . ")";
				if ($column->isUnsigned()) {
					$columnSql .= " UNSIGNED";
				}
				break;

			case Column::TYPE_DATE:
				if (empty ($columnSql)) {
					$columnSql .= "DATE";
				}
				break;

			case Column::TYPE_VARCHAR:
				if (empty ($columnSql)) {
					$columnSql .= "VARCHAR";
				}
				$columnSql .= "(" . $column->getSize() . ")";
				break;

			case Column::TYPE_DECIMAL:
				if (empty ($columnSql)) {
					$columnSql .= "DECIMAL";
				}
				$columnSql .= "(" . $column->getSize() . "," . $column->getScale() . ")";
				if ($column->isUnsigned()) {
					$columnSql .= " UNSIGNED";
				}
				break;

			case Column::TYPE_DATETIME:
				if (empty ($columnSql)) {
					$columnSql .= "DATETIME";
				}
				break;

			case Column::TYPE_TIMESTAMP:
				if (empty ($columnSql)) {
					$columnSql .= "TIMESTAMP";
				}
				break;

			case Column::TYPE_CHAR:
				if (empty ($columnSql)) {
					$columnSql .= "CHAR";
				}
				$columnSql .= "(" . $column->getSize() . ")";
				break;

			case Column::TYPE_TEXT:
				if (empty ($columnSql)) {
					$columnSql .= "TEXT";
				}
				break;

			case Column::TYPE_BOOLEAN:
				if (empty ($columnSql)) {
					$columnSql .= "TINYINT(1)";
				}
				break;

			case Column::TYPE_FLOAT:
				if (empty ($columnSql)) {
					$columnSql .= "FLOAT";
				}
				$size = $column->getSize();
				if ($size) {
					$scale = $column->getScale();
					if ($scale) {
						$columnSql .= "(" . $size . "," . $scale . ")";
					} else {
						$columnSql .= "(" . $size . ")";
					}
				}
				if ($column->isUnsigned()) {
					$columnSql .= " UNSIGNED";
				}
				break;

			case Column::TYPE_DOUBLE:
				if (empty ($columnSql)) {
					$columnSql .= "DOUBLE";
				}
				$size = $column->getSize();
				if ($size) {
					$scale = $column->getScale();
					$columnSql .= "(" . $size;
					if ($scale) {
						$columnSql .= "," . $scale . ")";
					} else {
						$columnSql .= ")";
					}
				}
				if ($column->isUnsigned()) {
					$columnSql .= " UNSIGNED";
				}
				break;

			case Column::TYPE_BIGINTEGER:
				if (empty ($columnSql)) {
					$columnSql .= "BIGINT";
				}
				$scale = $column->getSize();
				if ($scale) {
					$columnSql .= "(" . $column->getSize() . ")";
				}
				if ($column->isUnsigned()) {
					$columnSql .= " UNSIGNED";
				}
				break;

			case Column::TYPE_TINYBLOB:
				if (empty ($columnSql)) {
					$columnSql .= "TINYBLOB";
				}
				break;

			case Column::TYPE_BLOB:
				if (empty ($columnSql)) {
					$columnSql .= "BLOB";
				}
				break;

			case Column::TYPE_MEDIUMBLOB:
				if (empty ($columnSql)) {
					$columnSql .= "MEDIUMBLOB";
				}
				break;

			case Column::TYPE_LONGBLOB:
				if (empty ($columnSql)) {
					$columnSql .= "LONGBLOB";
				}
				break;

			default:
				if (empty ($columnSql)) {
					throw new Exception("Unrecognized MySQL data type at column " . $column->getName());
				}

				$typeValues = $column->getTypeValues();
				if (!empty ($typeValues)) {
					if (is_array($typeValues)) {
						
						$valueSql = "";
						foreach ($typeValues as $value) {
							$valueSql .= "\"" . addcslashes($value, "\"") . "\", ";
						}
						$columnSql .= "(" . substr($valueSql, 0, -2) . ")";
					} else {
						$columnSql .= "(\"" . addcslashes($typeValues, "\"") . "\")";
					}
				}
		}

		return $columnSql;
	}

	/**
	 * Generates SQL to add a column to a table
	 */
	public function addColumn($tableName, $schemaName, ColumnInterface $column) {

		$sql = "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " ADD `" . $column->getName() . "` " . $this->getColumnDefinition($column);

		if ($column->hasDefault()) {
			$defaultValue = $column->getDefault();
			if (memstr(strtoupper($defaultValue), "CURRENT_TIMESTAMP")) {
				$sql .= " DEFAULT CURRENT_TIMESTAMP";
			} else {
				$sql .= " DEFAULT \"" . addcslashes($defaultValue, "\"") . "\"";
			}
		}

		if ($column->isNotNull()) {
			$sql .= " NOT NULL";
		}

		if ($column->isAutoIncrement()) {
			$sql .= " AUTO_INCREMENT";
		}

		if ($column->isFirst()) {
			$sql .= " FIRST";
		} else {
			$afterPosition = $column->getAfterPosition();
			if ($afterPosition) {
				$sql .=  " AFTER `" . $afterPosition . "`";
			}
		}
		return $sql;
	}

	/**
	 * Generates SQL to modify a column in a table
	 */
	public function modifyColumn($tableName, $schemaName, ColumnInterface $column, ColumnInterface $currentColumn = null) {

		$sql = "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " MODIFY `" . $column->getName() . "` " . $this->getColumnDefinition($column);

		if ($column->hasDefault()) {
			$defaultValue = $column->getDefault();
			if (memstr(strtoupper($defaultValue), "CURRENT_TIMESTAMP")) {
				$sql .= " DEFAULT CURRENT_TIMESTAMP";
			} else {
				$sql .= " DEFAULT \"" . addcslashes($defaultValue, "\"") . "\"";
			}
		}

		if ($column->isNotNull()) {
			$sql .= " NOT NULL";
		}

		if ($column->isAutoIncrement()) {
			$sql .= " AUTO_INCREMENT";
		}

		if ($column->isFirst()) {
			$sql .= " FIRST";
		} else {
			$afterPosition = $column->getAfterPosition();
			if ($afterPosition) {
				$sql .=  " AFTER `" . $afterPosition . "`";
			}
		}
		return $sql;
	}

	/**
	 * Generates SQL to delete a column from a table
	 */
	public function dropColumn($tableName, $schemaName, $columnName) {
		return "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " DROP COLUMN `" . $columnName . "`";
	}

	/**
	 * Generates SQL to add an index to a table
	 */
	public function addIndex($tableName, $schemaName, IndexInterface $index) {
		
		$sql = "ALTER TABLE " . $this->prepareTable($tableName, $schemaName);

		$indexType = $index->getType();
		if (!empty ($indexType)) {
			$sql .= " ADD " . $indexType . " INDEX ";
		} else {
			$sql .= " ADD INDEX ";
		}

		$sql .= "`" . $index->getName() . "` (" . $this->getColumnList($index->getColumns()) . ")";
		return $sql;
	}

	/**
	 * Generates SQL to delete an index from a table
	 */
	public function dropIndex($tableName, $schemaName, $indexName) {
		return "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " DROP INDEX `" . $indexName . "`";
	}

	/**
	 * Generates SQL to add the primary key to a table
	 */
	public function addPrimaryKey($tableName, $schemaName, IndexInterface $index)
	{
		return "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " ADD PRIMARY KEY (" . $this->getColumnList($index->getColumns()) . ")";
	}

	/**
	 * Generates SQL to delete primary key from a table
	 */
	public function dropPrimaryKey($tableName, $schemaName)
	{
		return "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " DROP PRIMARY KEY";
	}

	/**
	 * Generates SQL to add an index to a table
	 */
	public function addForeignKey($tableName, $schemaName, ReferenceInterface $reference)
	{

		$sql = "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " ADD FOREIGN KEY `" . $reference->getName() . "`(" . $this->getColumnList($reference->getColumns()) . ") REFERENCES " . $this->prepareTable($reference->getReferencedTable(), $reference->getReferencedSchema()) . "(" . $this->getColumnList($reference->getReferencedColumns()) . ")";

		$onDelete = $reference->getOnDelete();
		if (!empty ($onDelete)) {
			$sql .= " ON DELETE " . $onDelete;
		}

		$onUpdate = $reference->getOnUpdate();
		if (!empty ($onUpdate)) {
			$sql .= " ON UPDATE " . $onUpdate;
		}

		return $sql;
	}

	/**
	 * Generates SQL to delete a foreign key from a table
	 */
	public function dropForeignKey($tableName, $schemaName, $referenceName)
	{
		return "ALTER TABLE " . $this->prepareTable($tableName, $schemaName) . " DROP FOREIGN KEY `" . $referenceName . "`";
	}

	/**
	 * Generates SQL to create a table
	 */
	public function createTable($tableName, $schemaName, array $definition)
	{

		if (!isset($definition["columns"])) {
			throw new Exception("The index 'columns' is required in the definition array");
		}
		$columns = $definition['columns'];

		$table = $this->prepareTable($tableName, $schemaName);

		$temporary = false;
		if (isset($definition["options"])) {
			$options = $definition["options"];
			$temporary = $options["temporary"];
		}

		/**
		 * Create a temporary or normal table
		 */
		if ($temporary) {
			$sql = "CREATE TABLE #" . $table . " (\n\t";
		} else {
			$sql = "CREATE TABLE " . $table . " (\n\t";
		}

		$createLines = [];
		foreach ($columns as $column) {

			$columnLine = "`" . $column->getName() . "` " . $this->getColumnDefinition($column);

			/**
			 * Add a Default clause
			 */
			if ($column->hasDefault()) {
				$defaultValue = $column->getDefault();
				if (memstr(strtoupper($defaultValue), "CURRENT_TIMESTAMP")) {
					$columnLine .= " DEFAULT CURRENT_TIMESTAMP";
				} else {
					$columnLine .= " DEFAULT \"" . addcslashes(defaultValue, "\"") . "\"";
				}
			}

			/**
			 * Add a NOT NULL clause
			 */
			if ($column->isNotNull()) {
				$columnLine .= " NOT NULL";
			}

			/**
			 * Add an AUTO_INCREMENT clause
			 */
			if ($column->isAutoIncrement()) {
				$columnLine .= " AUTO_INCREMENT";
			}

			/**
			 * Mark the column as primary key
			 */
			if ($column->isPrimary()) {
				$columnLine .= " PRIMARY KEY";
			}

			$createLines[] = $columnLine;
		}

		/**
		 * Create related indexes
		 */
		if (isset($definition["indexes"])) {

			foreach ($definition["indexes"] as $index) {

				$indexName = $index->getName();
				$indexType = $index->getType();

				/**
				 * If the index name is primary we add a primary key
				 */
				if ($indexName == "PRIMARY") {
					$indexSql = "PRIMARY KEY (" . $this->getColumnList($index->getColumns()) . ")";
				} else {
					if (!empty ($indexType)) {
						$indexSql = $indexType . " KEY `" . $indexName . "` (" . $this->getColumnList($index->getColumns()) . ")";
					} else {
						$indexSql = "KEY `" . $indexName . "` (" . $this->getColumnList($index->getColumns()) . ")";
					}
				}

				$createLines[] = $indexSql;
			}
		}

		/**
		 * Create related references
		 */
		if (isset($definition["references"])) {
			foreach ($definition["references"] as $reference) {
				$referenceSql = "CONSTRAINT `" . $reference->getName() . "` FOREIGN KEY (" . $this->getColumnList($reference->getColumns()) . ")"
					. " REFERENCES `" . $reference->getReferencedTable() . "`(" . $this->getColumnList($reference->getReferencedColumns()) . ")";

				$onDelete = $reference->getOnDelete();
				if (!empty($onDelete)) {
					$referenceSql .= " ON DELETE " . $onDelete;
				}

				$onUpdate = $reference->getOnUpdate();
				if (!empty($onUpdate)) {
					$referenceSql .= " ON UPDATE " . $onUpdate;
				}

				$createLines[] = $referenceSql;
			}
		}

		$sql .= join(",\n\t", $createLines) . "\n)";
		if (isset ($definition["options"])) {
			$sql .= " " . $this->_getTableOptions($definition);
		}

		return $sql;
	}

	/**
	 * Generates SQL to drop a table
	 */
	public function dropTable($tableName, $schemaName = null, $ifExists = true) {

		$table = "N'".$this->prepareTable($tableName, $schemaName)."'";

		if ($ifExists)
			return $this->_getIfObjectExists($tableName).' DROP TABLE '.$table;
		else 
			return "DROP TABLE ".$table;
	}

	/**
	 * Generates SQL to create a view
	 */
	public function createView($viewName, array $definition,  $schemaName = null) {

		if (!isset($definition["sql"])) {
			throw new Exception("The index 'sql' is required in the definition array");
		}

		return "CREATE VIEW " . $this->prepareTable($viewName, $schemaName) . " AS " . $definition["sql"];
	}

	/**
	 * Generates SQL to drop a view
	 */
	public function dropView($viewName, $schemaName = null, $ifExists = true) {

		$view = "N'".$this->prepareTable($viewName, $schemaName)."'";

		if ($ifExists)
			return $this->_getIfObjectExists($view, 'V').' DROP VIEW '.$view;
		else
			return 'DROP VIEW '.$view;
	}

	/**
	 * Generates SQL checking for the existence of a schema.table
	 *
	 * <code>
	 *    echo $dialect->tableExists("posts", "blog");
	 *    echo $dialect->tableExists("posts");
	 * </code>
	 */
	public function tableExists($tableName, $schemaName = null) {
		$sql = 'SELECT IF(COUNT(*) > 0, 1, 0) FROM [INFORMATION_SCHEMA].[TABLES] WHERE  [TABLE_TYPE] = "USER TABLE" AND [TABLE_NAME] = "'.$tableName.'" AND [TABLE_SCHEMA] = ';

		if ($schemaName)
			$sql .= '"'.$schemaName.'"';
		else 
			$sql .= 'DATABASE()';

		return $sql;
	}

	/**
	 * Generates SQL checking for the existence of a schema.view
	 */
	public function viewExists($viewName, $schemaName = null) {

		$sql = 'SELECT IF(COUNT(*) > 0, 1, 0) FROM [INFORMATION_SCHEMA].[TABLES] WHERE [TABLE_TYPE] = "VIEW" AND [TABLE_NAME] = "'.$viewName.'"  AND [TABLE_SCHEMA] = ';
		
		if ($schemaName)
			$sql .= '"'.$schemaName.'"';
		else
			$sql .= 'DATABASE()';

		return $sql;
	}

	/**
	 * Generates SQL describing a table
	 *
	 * <code>
	 *    print_r($dialect->describeColumns("posts"));
	 * </code>
	 */
	public function describeColumns($table, $schema = null) {
		//return "DESCRIBE " . $this->prepareTable($table, $schema);
		return 'EXEC sp_columns '.$this->prepareTable($table, $schema);
	}

	/**
	 * List all tables in database
	 *
	 * <code>
	 *     print_r($dialect->listTables("blog"))
	 * </code>
	 */
	public function listTables($schemaName = null) {
		$sql = 'SELECT [TABLE_NAME] FROM [INFORMATION_SCHEMA].[TABLES] WHERE [TABLE_TYPE] = "BASE TABLE"';
		if ($schemaName) {
			$sql .= 'AND [TABLE_SCHEMA] = "' . $schemaName . '"';
		}
		return $sql . ' ORDER BY [TABLE_NAME]';
	}

	/**
	 * Generates SQL to query indexes on a table
	 */
	public function describeIndexes($table, $schema = null) {

		$sql = 'SELECT DB_NAME() AS Database_Name, sc.name AS Schema_Name, o.name AS Table_Name, i.name AS Index_Name, i.type_desc AS Index_Type';
		$sql .= ' FROM '.$this->prepareTable('indexes', 'sys', 'i').' INNER JOIN '.$this->prepareTable('objects', 'sys', 'o').' ON i.object_id = o.object_id INNER JOIN '.$this->prepareTable('schemas', 'sys', 'sc').' ON o.schema_id = sc.schema_id ';
		$sql .= ' WHERE i.name IS NOT NULL AND o.type = "U" AND Table_Name = "'.$table.'" ';
		if ($schemas)
			$sql .= ' AND Schema_Name = "'.$schema.'"';
		$sql .= ' ORDER BY o.name, i.type';
		
		return $sql;
	}

	/**
	 * Generates SQL to query foreign keys on a table
	 */
	public function describeReferences($table, $schema = null) {
		$sql = "SELECT KCU.TABLE_NAME, KCU.COLUMN_NAME, KCU.CONSTRAINT_NAME, KCU.REFERENCED_TABLE_SCHEMA, KCU.REFERENCED_TABLE_NAME, KCU.REFERENCED_COLUMN_NAME, RC.UPDATE_RULE, RC.DELETE_RULE FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS RC ON RC.CONSTRAINT_NAME = KCU.CONSTRAINT_NAME WHERE KCU.REFERENCED_TABLE_NAME IS NOT NULL AND ";
		if ($schema) {
			$sql .= "KCU.CONSTRAINT_SCHEMA = '" . $schema . "' AND KCU.TABLE_NAME = '" . $table . "'";
		} else {
			$sql .= "KCU.TABLE_NAME = '" . $table . "'";
		}
		return $sql;
	}

	/**
	 * Generates the SQL to describe the table creation options
	 */
	public function tableOptions($table, $schema = null) {
		$sql = "SELECT TABLES.TABLE_TYPE AS table_type,TABLES.AUTO_INCREMENT AS auto_increment,TABLES.ENGINE AS engine,TABLES.TABLE_COLLATION AS table_collation FROM INFORMATION_SCHEMA.TABLES WHERE ";
		if ($schema) {
			return $sql . "TABLES.TABLE_SCHEMA = '" . $schema . "' AND TABLES.TABLE_NAME = '" . $table . "'";
		}
		return $sql . "TABLES.TABLE_NAME = '" . $table . "'";
	}


	protected function _getIfObjectExists($name, $type = 'U') {
		/*
		From https://technet.microsoft.com/en-us/library/ms190324.aspx
		AF = Aggregate function (CLR)
		C = CHECK constraint
		D = DEFAULT (constraint or stand-alone)
		F = FOREIGN KEY constraint
		FN = SQL scalar function
		FS = Assembly (CLR) scalar-function
		FT = Assembly (CLR) table-valued function
		IF = SQL inline table-valued function
		IT = Internal table
		P = SQL Stored Procedure
		PC = Assembly (CLR) stored-procedure
		PG = Plan guide
		PK = PRIMARY KEY constraint
		R = Rule (old-style, stand-alone)
		RF = Replication-filter-procedure
		S = System base table
		SN = Synonym
		SO = Sequence object
		SQ = Service queue
		TA = Assembly (CLR) DML trigger
		TF = SQL table-valued-function
		TR = SQL DML trigger
		TT = Table type
		U = Table (user-defined)
		UQ = UNIQUE constraint
		V = View
		X = Extended stored procedure
		*/
		return 'IF OBJECT_ID('.$name.', "'.$type.'") IS NOT NULL';
	}

	/**
	 * Generates SQL to add the table creation options
	 */
	protected function _getTableOptions($definition) {
		
		if (isset($definition["options"])) {
			$options = $definition["options"];

			$tableOptions = [];

			/**
			 * Check if there is an ENGINE option
			 */
			if (!empty($options["ENGINE"])) {
				$tableOptions[] = "ENGINE=" . $options["ENGINE"];
			}

			/**
			 * Check if there is an AUTO_INCREMENT option
			 */
			if (!empty($options["AUTO_INCREMENT"])) {
				$tableOptions[] = "AUTO_INCREMENT=" . $options["AUTO_INCREMENT"];
			}

			/**
			 * Check if there is a TABLE_COLLATION option
			 */
			if (!empty($options["TABLE_COLLATION"])) {
				$collationParts = explode("_", $options["TABLE_COLLATION"]);
				$tableOptions[] = "DEFAULT CHARSET=" . $collationParts[0];
				$tableOptions[] = "COLLATE=" . $options["TABLE_COLLATION"];
			}

			if (count($tableOptions)) {
				return join(" ", $tableOptions);
			}
		}

		return "";
	}
}