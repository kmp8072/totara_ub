<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * MSSQL specific SQL code generator.
 *
 * @package    core_ddl
 * @copyright  1999 onwards Martin Dougiamas     http://dougiamas.com
 *             2001-3001 Eloy Lafuente (stronk7) http://contiento.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/ddl/sql_generator.php');

/**
 * This class generate SQL code to be used against MSSQL
 * It extends XMLDBgenerator so everything can be
 * overridden as needed to generate correct SQL.
 *
 * @package    core_ddl
 * @copyright  1999 onwards Martin Dougiamas     http://dougiamas.com
 *             2001-3001 Eloy Lafuente (stronk7) http://contiento.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mssql_sql_generator extends sql_generator {

    // Only set values that are different from the defaults present in XMLDBgenerator

    /** @var string To be automatically added at the end of each statement. */
    public $statement_end = "\ngo";

    /** @var string Proper type for NUMBER(x) in this DB. */
    public $number_type = 'DECIMAL';

    /** @var string To define the default to set for NOT NULLs CHARs without default (null=do nothing).*/
    public $default_for_char = '';

    /**
     * @var bool To force the generator if NULL clauses must be specified. It shouldn't be necessary.
     * note: some mssql drivers require them or everything is created as NOT NULL :-(
     */
    public $specify_nulls = true;

    /** @var bool True if the generator needs to add extra code to generate the sequence fields.*/
    public $sequence_extra_code = false;

    /** @var string The particular name for inline sequences in this generator.*/
    public $sequence_name = 'IDENTITY(1,1)';

    /** @var bool To avoid outputting the rest of the field specs, leaving only the name and the sequence_name returned.*/
    public $sequence_only = false;

    /** @var bool True if the generator needs to add code for table comments.*/
    public $add_table_comments = false;

    /** @var string Characters to be used as concatenation operator.*/
    public $concat_character = '+';

    /** @var string SQL sentence to rename one table, both 'OLDNAME' and 'NEWNAME' keywords are dynamically replaced.*/
    public $rename_table_sql = "sp_rename 'OLDNAME', 'NEWNAME'";

    /** @var string SQL sentence to rename one column where 'TABLENAME', 'OLDFIELDNAME' and 'NEWFIELDNAME' keywords are dynamically replaced.*/
    public $rename_column_sql = "sp_rename 'TABLENAME.OLDFIELDNAME', 'NEWFIELDNAME', 'COLUMN'";

    /** @var string SQL sentence to drop one index where 'TABLENAME', 'INDEXNAME' keywords are dynamically replaced.*/
    public $drop_index_sql = 'DROP INDEX TABLENAME.INDEXNAME';

    /** @var string SQL sentence to rename one index where 'TABLENAME', 'OLDINDEXNAME' and 'NEWINDEXNAME' are dynamically replaced.*/
    public $rename_index_sql = "sp_rename 'TABLENAME.OLDINDEXNAME', 'NEWINDEXNAME', 'INDEX'";

    /** @var string SQL sentence to rename one key 'TABLENAME', 'OLDKEYNAME' and 'NEWKEYNAME' are dynamically replaced.*/
    public $rename_key_sql = null;

    /**
     * Reset a sequence to the id field of a table.
     *
     * @param xmldb_table|string $table name of table or the table object.
     * @param int $offset the next id offset
     * @return array of sql statements
     */
    public function getResetSequenceSQL($table, $offset = 0) {

        if (is_string($table)) {
            $table = new xmldb_table($table);
        }
        $tablename = $this->getTableName($table);

        $value = (int)$this->mdb->get_field_sql("SELECT MAX(id) FROM {$tablename}");

        $identityinfo = $this->mdb->get_identity_info($table->getName());

        $sqls = array();

        $value = $value + $offset;
        if (!$identityinfo) {
            debugging('Reading of current identity information failed for table: ' . $table->getName());
            $value = $value + 1;
        } else if ($identityinfo[0] === 'NULL') {
            $value = $value + 1;
        }

        // From http://msdn.microsoft.com/en-us/library/ms176057.aspx
        $sqls[] = "DBCC CHECKIDENT ('{$tablename}', RESEED, {$value})";

        return $sqls;
    }

    /**
     * Given one xmldb_table, returns it's correct name, depending of all the parametrization
     * Overridden to allow change of names in temp tables
     *
     * @param xmldb_table table whose name we want
     * @param boolean to specify if the name must be quoted (if reserved word, only!)
     * @return string the correct name of the table
     */
    public function getTableName(xmldb_table $xmldb_table, $quoted=true) {
        // Get the name, supporting special mssql names for temp tables
        if ($this->temptables->is_temptable($xmldb_table->getName())) {
            $tablename = $this->temptables->get_correct_name($xmldb_table->getName());
        } else {
            $tablename = $this->prefix . $xmldb_table->getName();
        }

        // Apply quotes optionally
        if ($quoted) {
            $tablename = $this->getEncQuoted($tablename);
        }

        return $tablename;
    }

    /**
     * Given one correct xmldb_table, returns the SQL statements
     * to create temporary table (inside one array).
     *
     * @param xmldb_table $xmldb_table The xmldb_table object instance.
     * @return array of sql statements
     */
    public function getCreateTempTableSQL($xmldb_table) {
        $this->temptables->add_temptable($xmldb_table->getName());
        $sqlarr = $this->getCreateTableSQL($xmldb_table);
        return $sqlarr;
    }

    /**
     * Given one correct xmldb_table, returns the SQL statements
     * to drop it (inside one array).
     *
     * @param xmldb_table $xmldb_table The table to drop.
     * @return array SQL statement(s) for dropping the specified table.
     */
    public function getDropTableSQL($xmldb_table) {
        $sqlarr = parent::getDropTableSQL($xmldb_table);
        if ($this->temptables->is_temptable($xmldb_table->getName())) {
            $this->temptables->delete_temptable($xmldb_table->getName());
        }
        return $sqlarr;
    }

    /**
     * Given one XMLDB Type, length and decimals, returns the DB proper SQL type.
     *
     * @param int $xmldb_type The xmldb_type defined constant. XMLDB_TYPE_INTEGER and other XMLDB_TYPE_* constants.
     * @param int $xmldb_length The length of that data type.
     * @param int $xmldb_decimals The decimal places of precision of the data type.
     * @return string The DB defined data type.
     */
    public function getTypeSQL($xmldb_type, $xmldb_length=null, $xmldb_decimals=null) {

        switch ($xmldb_type) {
            case XMLDB_TYPE_INTEGER:    // From http://msdn.microsoft.com/library/en-us/tsqlref/ts_da-db_7msw.asp?frame=true
                if (empty($xmldb_length)) {
                    $xmldb_length = 10;
                }
                if ($xmldb_length > 9) {
                    $dbtype = 'BIGINT';
                } else if ($xmldb_length > 4) {
                    $dbtype = 'INTEGER';
                } else {
                    $dbtype = 'SMALLINT';
                }
                break;
            case XMLDB_TYPE_NUMBER:
                $dbtype = $this->number_type;
                if (!empty($xmldb_length)) {
                    // 38 is the max allowed
                    if ($xmldb_length > 38) {
                        $xmldb_length = 38;
                    }
                    $dbtype .= '(' . $xmldb_length;
                    if (!empty($xmldb_decimals)) {
                        $dbtype .= ',' . $xmldb_decimals;
                    }
                    $dbtype .= ')';
                }
                break;
            case XMLDB_TYPE_FLOAT:
                $dbtype = 'FLOAT';
                if (!empty($xmldb_decimals)) {
                    if ($xmldb_decimals < 6) {
                        $dbtype = 'REAL';
                    }
                }
                break;
            case XMLDB_TYPE_CHAR:
                $dbtype = 'NVARCHAR';
                if (empty($xmldb_length)) {
                    $xmldb_length='255';
                }
                $dbtype .= '(' . $xmldb_length . ') COLLATE database_default';
                break;
            case XMLDB_TYPE_TEXT:
                $dbtype = 'NVARCHAR(MAX) COLLATE database_default';
                break;
            case XMLDB_TYPE_BINARY:
                $dbtype = 'VARBINARY(MAX)';
                break;
            case XMLDB_TYPE_DATETIME:
                $dbtype = 'DATETIME';
                break;
        }
        return $dbtype;
    }

    /**
     * Given one xmldb_table and one xmldb_field, return the SQL statements needed to drop the field from the table.
     * MSSQL overwrites the standard sentence because it needs to do some extra work dropping the default and
     * check constraints
     *
     * @param xmldb_table $xmldb_table The table related to $xmldb_field.
     * @param xmldb_field $xmldb_field The instance of xmldb_field to create the SQL from.
     * @return array The SQL statement for dropping a field from the table.
     */
    public function getDropFieldSQL($xmldb_table, $xmldb_field) {
        $results = array();

        // Get the quoted name of the table and field
        $tablename = $this->getTableName($xmldb_table);
        $fieldname = $this->getEncQuoted($xmldb_field->getName());

        // Look for any default constraint in this field and drop it
        if ($defaultname = $this->getDefaultConstraintName($xmldb_table, $xmldb_field)) {
            $results[] = 'ALTER TABLE ' . $tablename . ' DROP CONSTRAINT ' . $defaultname;
        }

        // Build the standard alter table drop column
        $results[] = 'ALTER TABLE ' . $tablename . ' DROP COLUMN ' . $fieldname;

        return $results;
    }

    /**
     * Given one correct xmldb_field and the new name, returns the SQL statements
     * to rename it (inside one array).
     *
     * MSSQL is special, so we overload the function here. It needs to
     * drop the constraints BEFORE renaming the field
     *
     * @param xmldb_table $xmldb_table The table related to $xmldb_field.
     * @param xmldb_field $xmldb_field The instance of xmldb_field to get the renamed field from.
     * @param string $newname The new name to rename the field to.
     * @return array The SQL statements for renaming the field.
     */
    public function getRenameFieldSQL($xmldb_table, $xmldb_field, $newname) {

        $results = array();  //Array where all the sentences will be stored

        // Although this is checked in database_manager::rename_field() - double check
        // that we aren't trying to rename one "id" field. Although it could be
        // implemented (if adding the necessary code to rename sequences, defaults,
        // triggers... and so on under each getRenameFieldExtraSQL() function, it's
        // better to forbid it, mainly because this field is the default PK and
        // in the future, a lot of FKs can be pointing here. So, this field, more
        // or less, must be considered immutable!
        if ($xmldb_field->getName() == 'id') {
            return array();
        }

        // Call to standard (parent) getRenameFieldSQL() function
        $results = array_merge($results, parent::getRenameFieldSQL($xmldb_table, $xmldb_field, $newname));

        // Totara: remove double quotes around reserved words, SQL Server is using single quotes already.
        foreach ($results as $k => $v) {
            $results[$k] = str_replace('"', '', $v);
        }

        return $results;
    }

    /**
     * Returns the code (array of statements) needed to execute extra statements on table rename.
     *
     * @param xmldb_table $xmldb_table The xmldb_table object instance.
     * @param string $newname The new name for the table.
     * @return array Array of extra SQL statements to rename a table.
     */
    public function getRenameTableExtraSQL($xmldb_table, $newname) {

        $results = array();

        return $results;
    }

    /**
     * Given one correct xmldb_index, returns the SQL statements
     * needed to create it (in array).
     *
     * @param xmldb_table $xmldb_table The xmldb_table instance to create the index on.
     * @param xmldb_index $xmldb_index The xmldb_index to create.
     * @return array An array of SQL statements to create the index.
     * @throws coding_exception Thrown if the xmldb_index does not validate with the xmldb_table.
     */
    public function getCreateIndexSQL($xmldb_table, $xmldb_index) {
        // Totara: allow unique index on nullable columns ignoring the nulls.
        if ($error = $xmldb_index->validateDefinition($xmldb_table)) {
            throw new coding_exception($error);
        }

        $hints = $xmldb_index->getHints();
        $fields = $xmldb_index->getFields();
        if (in_array('full_text_search', $hints)) {
            $tablename = $this->getTableName($xmldb_table);
            $fieldname = reset($fields);

            // Note that accessing database at this stage is not allowed because we create list of sql commands before execution.
            $sqls = array();

            // Create search catalogue for this instance if it does not exist.
            $prefix = $this->mdb->get_prefix();
            $sqls[] = "IF NOT EXISTS (SELECT 1 FROM sys.fulltext_catalogs WHERE name = '{$prefix}search_catalog') 
                         BEGIN
                           CREATE FULLTEXT CATALOG {$prefix}search_catalog
                         END";
            $indexname = $this->getNameForObject($xmldb_table->getName(), 'id', 'fts'); // Yes, 'id' is corect here because it is shared by all full text search indices.
            $language = $this->mdb->get_ftslanguage();
            // Microsoft is using either language code numbers or names of languages.
            if (is_number($language)) {
                $language = intval($language);
            } else {
                $language = "'$language'";
            }

            // Add required unique index if it does not exist yet.
            $sqls[] = "IF NOT EXISTS (SELECT 1
                                        FROM sys.indexes i
                                        JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
                                        JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
                                        JOIN sys.tables t ON i.object_id = t.object_id
                                       WHERE t.name = '{$tablename}' AND i.name = '{$indexname}' AND c.name = 'id') 
                         BEGIN
                           CREATE UNIQUE INDEX {$indexname} ON {$tablename}(id) 
                         END";

            $sqls[] = "IF EXISTS (SELECT 1
                                    FROM sys.fulltext_indexes i
                                    JOIN sys.fulltext_index_columns ic ON i.object_id = ic.object_id
                                    JOIN sys.tables t ON i.object_id = t.object_id
                                    JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
                                   WHERE t.name = '{$tablename}')
                         BEGIN
                           ALTER FULLTEXT INDEX ON {$tablename} ADD ({$fieldname} Language {$language})
                         END
                       ELSE
                         BEGIN
                           IF EXISTS (SELECT 1
                                        FROM sys.fulltext_indexes i
                                        JOIN sys.tables t ON i.object_id = t.object_id
                                       WHERE t.name = '{$tablename}')
                             BEGIN
                               ALTER FULLTEXT INDEX ON {$tablename} ADD ({$fieldname} Language {$language})
                             END
                           ELSE
                             BEGIN
                               CREATE FULLTEXT INDEX ON {$tablename} ({$fieldname} Language {$language})
                                 KEY INDEX {$indexname} ON {$prefix}search_catalog WITH CHANGE_TRACKING AUTO
                             END 
                         END";

            return $sqls;
        }

        // NOTE: quiz_report table has a messed up nullable name field, ignore it.

        if ($xmldb_index->getUnique() and count($xmldb_index->getFields()) === 1 and $xmldb_table->getName() !== 'quiz_reports') {
            $fields = $xmldb_index->getFields();
            $fieldname = reset($fields);
            /** @var xmldb_field $field */
            $field = $xmldb_table->getField($fieldname);
            if ($field and !$field->getNotNull()) {
                $unique = ' UNIQUE';
                $suffix = 'uix';
                $index = 'CREATE' . $unique . ' INDEX ';
                $index .= $this->getNameForObject($xmldb_table->getName(), implode(', ', $xmldb_index->getFields()), $suffix);
                $index .= ' ON ' . $this->getTableName($xmldb_table);
                $index .= ' (' . implode(', ', $this->getEncQuoted($xmldb_index->getFields())) . ')';
                $index .= ' WHERE '. $this->getEncQuoted($fieldname) . ' IS NOT NULL';
                return array($index);
            }
        }
        return parent::getCreateIndexSQL($xmldb_table, $xmldb_index);
    }

    /**
     * Given one xmldb_table and one xmldb_index, return the SQL statements needed to drop the index from the table.
     *
     * @param xmldb_table $xmldb_table The xmldb_table instance to drop the index on.
     * @param xmldb_index $xmldb_index The xmldb_index to drop.
     * @return array An array of SQL statements to drop the index.
     */
    public function getDropIndexSQL($xmldb_table, $xmldb_index) {
        if (in_array('full_text_search', $xmldb_index->getHints())) {
            $results = array();
            $tablename = $this->getTableName($xmldb_table);
            $fieldname = $xmldb_index->getFields()[0];
            $results[] = "ALTER FULLTEXT INDEX ON {$tablename} DROP ({$fieldname})";
            return $results;
        }

        return parent::getDropIndexSQL($xmldb_table, $xmldb_index);
    }

    /**
     * Given one xmldb_table and one xmldb_field, return the SQL statements needed to alter the field in the table.
     *
     * @param xmldb_table $xmldb_table The table related to $xmldb_field.
     * @param xmldb_field $xmldb_field The instance of xmldb_field to create the SQL from.
     * @param string $skip_type_clause The type clause on alter columns, NULL by default.
     * @param string $skip_default_clause The default clause on alter columns, NULL by default.
     * @param string $skip_notnull_clause The null/notnull clause on alter columns, NULL by default.
     * @return string The field altering SQL statement.
     */
    public function getAlterFieldSQL($xmldb_table, $xmldb_field, $skip_type_clause = NULL, $skip_default_clause = NULL, $skip_notnull_clause = NULL) {

        $results = array();     // To store all the needed SQL commands

        // Get the quoted name of the table and field
        $tablename = $xmldb_table->getName();
        $fieldname = $xmldb_field->getName();

        // Take a look to field metadata
        $meta = $this->mdb->get_columns($tablename);
        $metac = $meta[$fieldname];
        $oldmetatype = $metac->meta_type;

        $oldlength = $metac->max_length;
        $olddecimals = empty($metac->scale) ? null : $metac->scale;
        $oldnotnull = empty($metac->not_null) ? false : $metac->not_null;
        //$olddefault = empty($metac->has_default) ? null : strtok($metac->default_value, ':');

        $typechanged = true;  //By default, assume that the column type has changed
        $lengthchanged = true;  //By default, assume that the column length has changed

        // Detect if we are changing the type of the column
        if (($xmldb_field->getType() == XMLDB_TYPE_INTEGER && $oldmetatype == 'I') ||
            ($xmldb_field->getType() == XMLDB_TYPE_NUMBER  && $oldmetatype == 'N') ||
            ($xmldb_field->getType() == XMLDB_TYPE_FLOAT   && $oldmetatype == 'F') ||
            ($xmldb_field->getType() == XMLDB_TYPE_CHAR    && $oldmetatype == 'C') ||
            ($xmldb_field->getType() == XMLDB_TYPE_TEXT    && $oldmetatype == 'X') ||
            ($xmldb_field->getType() == XMLDB_TYPE_BINARY  && $oldmetatype == 'B')) {
            $typechanged = false;
        }

        // If the new field (and old) specs are for integer, let's be a bit more specific differentiating
        // types of integers. Else, some combinations can cause things like MDL-21868
        if ($xmldb_field->getType() == XMLDB_TYPE_INTEGER && $oldmetatype == 'I') {
            if ($xmldb_field->getLength() > 9) { // Convert our new lenghts to detailed meta types
                $newmssqlinttype = 'I8';
            } else if ($xmldb_field->getLength() > 4) {
                $newmssqlinttype = 'I';
            } else {
                $newmssqlinttype = 'I2';
            }
            if ($metac->type == 'bigint') { // Convert current DB type to detailed meta type (our metatype is not enough!)
                $oldmssqlinttype = 'I8';
            } else if ($metac->type == 'smallint') {
                $oldmssqlinttype = 'I2';
            } else {
                $oldmssqlinttype = 'I';
            }
            if ($newmssqlinttype != $oldmssqlinttype) { // Compare new and old meta types
                $typechanged = true; // Change in meta type means change in type at all effects
            }
        }

        // Detect if we are changing the length of the column, not always necessary to drop defaults
        // if only the length changes, but it's safe to do it always
        if ($xmldb_field->getLength() == $oldlength) {
            $lengthchanged = false;
        }

        // If type or length have changed drop the default if exists
        if ($typechanged || $lengthchanged) {
            $results = $this->getDropDefaultSQL($xmldb_table, $xmldb_field);
        }

        // Some changes of type require multiple alter statements, because mssql lacks direct implicit cast between such types
        // Here it is the matrix: http://msdn.microsoft.com/en-us/library/ms187928(SQL.90).aspx
        // Going to store such intermediate alters in array of objects, storing all the info needed
        $multiple_alter_stmt = array();
        $targettype = $xmldb_field->getType();

        if ($targettype == XMLDB_TYPE_TEXT && $oldmetatype == 'I') { // integer to text
            $multiple_alter_stmt[0] = new stdClass;                  // needs conversion to varchar
            $multiple_alter_stmt[0]->type = XMLDB_TYPE_CHAR;
            $multiple_alter_stmt[0]->length = 255;

        } else if ($targettype == XMLDB_TYPE_TEXT && $oldmetatype == 'N') { // decimal to text
            $multiple_alter_stmt[0] = new stdClass;                         // needs conversion to varchar
            $multiple_alter_stmt[0]->type = XMLDB_TYPE_CHAR;
            $multiple_alter_stmt[0]->length = 255;

        } else if ($targettype == XMLDB_TYPE_TEXT && $oldmetatype == 'F') { // float to text
            $multiple_alter_stmt[0] = new stdClass;                         // needs conversion to varchar
            $multiple_alter_stmt[0]->type = XMLDB_TYPE_CHAR;
            $multiple_alter_stmt[0]->length = 255;

        } else if ($targettype == XMLDB_TYPE_INTEGER && $oldmetatype == 'X') { // text to integer
            $multiple_alter_stmt[0] = new stdClass;                            // needs conversion to varchar
            $multiple_alter_stmt[0]->type = XMLDB_TYPE_CHAR;
            $multiple_alter_stmt[0]->length = 255;
            $multiple_alter_stmt[1] = new stdClass;                            // and also needs conversion to decimal
            $multiple_alter_stmt[1]->type = XMLDB_TYPE_NUMBER;                 // without decimal positions
            $multiple_alter_stmt[1]->length = 10;

        } else if ($targettype == XMLDB_TYPE_NUMBER && $oldmetatype == 'X') { // text to decimal
            $multiple_alter_stmt[0] = new stdClass;                           // needs conversion to varchar
            $multiple_alter_stmt[0]->type = XMLDB_TYPE_CHAR;
            $multiple_alter_stmt[0]->length = 255;

        } else if ($targettype ==  XMLDB_TYPE_FLOAT && $oldmetatype == 'X') { // text to float
            $multiple_alter_stmt[0] = new stdClass;                           // needs conversion to varchar
            $multiple_alter_stmt[0]->type = XMLDB_TYPE_CHAR;
            $multiple_alter_stmt[0]->length = 255;
        }

        // Just prevent default clauses in this type of sentences for mssql and launch the parent one
        if (empty($multiple_alter_stmt)) { // Direct implicit conversion allowed, launch it
            $results = array_merge($results, parent::getAlterFieldSQL($xmldb_table, $xmldb_field, NULL, true, NULL));

        } else { // Direct implicit conversion forbidden, use the intermediate ones
            $final_type = $xmldb_field->getType(); // Save final type and length
            $final_length = $xmldb_field->getLength();
            foreach ($multiple_alter_stmt as $alter) {
                $xmldb_field->setType($alter->type);  // Put our intermediate type and length and alter to it
                $xmldb_field->setLength($alter->length);
                $results = array_merge($results, parent::getAlterFieldSQL($xmldb_table, $xmldb_field, NULL, true, NULL));
            }
            $xmldb_field->setType($final_type); // Set the final type and length and alter to it
            $xmldb_field->setLength($final_length);
            $results = array_merge($results, parent::getAlterFieldSQL($xmldb_table, $xmldb_field, NULL, true, NULL));
        }

        // Finally, process the default clause to add it back if necessary
        if ($typechanged || $lengthchanged) {
            $results = array_merge($results, $this->getCreateDefaultSQL($xmldb_table, $xmldb_field));
        }

        // Return results
        return $results;
    }

    /**
     * Given one xmldb_table and one xmldb_field, return the SQL statements needed to modify the default of the field in the table.
     *
     * @param xmldb_table $xmldb_table The table related to $xmldb_field.
     * @param xmldb_field $xmldb_field The instance of xmldb_field to get the modified default value from.
     * @return array The SQL statement for modifying the default value.
     */
    public function getModifyDefaultSQL($xmldb_table, $xmldb_field) {
        // MSSQL is a bit special with default constraints because it implements them as external constraints so
        // normal ALTER TABLE ALTER COLUMN don't work to change defaults. Because this, we have this method overloaded here

        $results = array();

        // Decide if we are going to create/modify or to drop the default
        if ($xmldb_field->getDefault() === null) {
            $results = $this->getDropDefaultSQL($xmldb_table, $xmldb_field); //Drop but, under some circumstances, re-enable
            $default_clause = $this->getDefaultClause($xmldb_field);
            if ($default_clause) { //If getDefaultClause() it must have one default, create it
                $results = array_merge($results, $this->getCreateDefaultSQL($xmldb_table, $xmldb_field)); //Create/modify
            }
        } else {
            $results = $this->getDropDefaultSQL($xmldb_table, $xmldb_field); //Drop (only if exists)
            $results = array_merge($results, $this->getCreateDefaultSQL($xmldb_table, $xmldb_field)); //Create/modify
        }

        return $results;
    }

    /**
     * Given one xmldb_table and one xmldb_field, return the SQL statements needed to add its default
     * (usually invoked from getModifyDefaultSQL()
     *
     * @param xmldb_table $xmldb_table The xmldb_table object instance.
     * @param xmldb_field $xmldb_field The xmldb_field object instance.
     * @return array Array of SQL statements to create a field's default.
     */
    public function getCreateDefaultSQL($xmldb_table, $xmldb_field) {
        // MSSQL is a bit special and it requires the corresponding DEFAULT CONSTRAINT to be dropped

        $results = array();

        // Get the quoted name of the table and field
        $tablename = $this->getTableName($xmldb_table);
        $fieldname = $this->getEncQuoted($xmldb_field->getName());

        // Now, check if, with the current field attributes, we have to build one default
        $default_clause = $this->getDefaultClause($xmldb_field);
        if ($default_clause) {
            // We need to build the default (Moodle) default, so do it
            $sql = 'ALTER TABLE ' . $tablename . ' ADD' . $default_clause . ' FOR ' . $fieldname;
            $results[] = $sql;
        }

        return $results;
    }

    /**
     * Given one xmldb_table and one xmldb_field, return the SQL statements needed to drop its default
     * (usually invoked from getModifyDefaultSQL()
     *
     * Note that this method may be dropped in future.
     *
     * @param xmldb_table $xmldb_table The xmldb_table object instance.
     * @param xmldb_field $xmldb_field The xmldb_field object instance.
     * @return array Array of SQL statements to create a field's default.
     *
     * @todo MDL-31147 Moodle 2.1 - Drop getDropDefaultSQL()
     */
    public function getDropDefaultSQL($xmldb_table, $xmldb_field) {
        // MSSQL is a bit special and it requires the corresponding DEFAULT CONSTRAINT to be dropped

        $results = array();

        // Get the quoted name of the table and field
        $tablename = $this->getTableName($xmldb_table);
        $fieldname = $this->getEncQuoted($xmldb_field->getName());

        // Look for the default contraint and, if found, drop it
        if ($defaultname = $this->getDefaultConstraintName($xmldb_table, $xmldb_field)) {
            $results[] = 'ALTER TABLE ' . $tablename . ' DROP CONSTRAINT ' . $defaultname;
        }

        return $results;
    }

    /**
     * Given one xmldb_table and one xmldb_field, returns the name of its default constraint in DB
     * or false if not found
     * This function should be considered internal and never used outside from generator
     *
     * @param xmldb_table $xmldb_table The xmldb_table object instance.
     * @param xmldb_field $xmldb_field The xmldb_field object instance.
     * @return mixed
     */
    protected function getDefaultConstraintName($xmldb_table, $xmldb_field) {

        // Get the quoted name of the table and field
        $tablename = $this->getTableName($xmldb_table);
        $fieldname = $xmldb_field->getName();

        // Look for any default constraint in this field and drop it
        if ($default = $this->mdb->get_record_sql("SELECT object_id, object_name(default_object_id) AS defaultconstraint
                                                     FROM sys.columns
                                                    WHERE object_id = object_id(?)
                                                          AND name = ?", array($tablename, $fieldname))) {
            return $default->defaultconstraint;
        } else {
            return false;
        }
    }

    /**
     * Given three strings (table name, list of fields (comma separated) and suffix),
     * create the proper object name quoting it if necessary.
     *
     * IMPORTANT: This function must be used to CALCULATE NAMES of objects TO BE CREATED,
     *            NEVER TO GUESS NAMES of EXISTING objects!!!
     *
     * IMPORTANT: We are overriding this function for the MSSQL generator because objects
     * belonging to temporary tables aren't searchable in the catalog neither in information
     * schema tables. So, for temporary tables, we are going to add 4 randomly named "virtual"
     * fields, so the generated names won't cause concurrency problems. Really nasty hack,
     * but the alternative involves modifying all the creation table code to avoid naming
     * constraints for temp objects and that will dupe a lot of code.
     *
     * @param string $tablename The table name.
     * @param string $fields A list of comma separated fields.
     * @param string $suffix A suffix for the object name.
     * @return string Object's name.
     */
    public function getNameForObject($tablename, $fields, $suffix='') {
        if ($this->temptables->is_temptable($tablename)) { // Is temp table, inject random field names
            $random = strtolower(random_string(12)); // 12cc to be split in 4 parts
            $fields = $fields . ', ' . implode(', ', str_split($random, 3));
        }
        return parent::getNameForObject($tablename, $fields, $suffix); // Delegate to parent (common) algorithm
    }

    /**
     * Given one object name and it's type (pk, uk, fk, ck, ix, uix, seq, trg).
     *
     * (MySQL requires the whole xmldb_table object to be specified, so we add it always)
     *
     * This is invoked from getNameForObject().
     * Only some DB have this implemented.
     *
     * @param string $object_name The object's name to check for.
     * @param string $type The object's type (pk, uk, fk, ck, ix, uix, seq, trg).
     * @param string $table_name The table's name to check in
     * @return bool If such name is currently in use (true) or no (false)
     */
    public function isNameInUse($object_name, $type, $table_name) {
        switch($type) {
            case 'seq':
            case 'trg':
            case 'pk':
            case 'uk':
            case 'fk':
            case 'ck':
                if ($check = $this->mdb->get_records_sql("SELECT name
                                                            FROM sys.objects
                                                           WHERE lower(name) = ?", array(strtolower($object_name)))) {
                    return true;
                }
                break;
            case 'ix':
            case 'uix':
                if ($check = $this->mdb->get_records_sql("SELECT name
                                                            FROM sys.indexes
                                                           WHERE lower(name) = ?", array(strtolower($object_name)))) {
                    return true;
                }
                break;
        }
        return false; //No name in use found
    }

    /**
     * Returns the code (array of statements) needed to add one comment to the table.
     *
     * @param xmldb_table $xmldb_table The xmldb_table object instance.
     * @return array Array of SQL statements to add one comment to the table.
     */
    public function getCommentSQL($xmldb_table) {
        return array();
    }

    /**
     * Adds slashes to string.
     * @param string $s
     * @return string The escaped string.
     */
    public function addslashes($s) {
        // do not use php addslashes() because it depends on PHP quote settings!
        $s = str_replace("'",  "''", $s);
        return $s;
    }

    /**
     * Returns an array of reserved words (lowercase) for this DB
     *
     * https://docs.microsoft.com/en-us/sql/t-sql/language-elements/reserved-keywords-transact-sql
     *
     * @return array An array of database specific reserved words
     */
    public static function getReservedWords() {
        // This file contains the reserved words for MSSQL databases
        // from http://msdn2.microsoft.com/en-us/library/ms189822.aspx
        $reserved_words = array (
            'ADD',
            'ALL',
            'ALTER',
            'AND',
            'ANY',
            'AS',
            'ASC',
            'AUTHORIZATION',
            'BACKUP',
            'BEGIN',
            'BETWEEN',
            'BREAK',
            'BROWSE',
            'BULK',
            'BY',
            'CASCADE',
            'CASE',
            'CHECK',
            'CHECKPOINT',
            'CLOSE',
            'CLUSTERED',
            'COALESCE',
            'COLLATE',
            'COLUMN',
            'COMMIT',
            'COMPUTE',
            'CONSTRAINT',
            'CONTAINS',
            'CONTAINSTABLE',
            'CONTINUE',
            'CONVERT',
            'CREATE',
            'CROSS',
            'CURRENT',
            'CURRENT_DATE',
            'CURRENT_TIME',
            'CURRENT_TIMESTAMP',
            'CURRENT_USER',
            'CURSOR',
            'DATABASE',
            'DBCC',
            'DEALLOCATE',
            'DECLARE',
            'DEFAULT',
            'DELETE',
            'DENY',
            'DESC',
            'DISK',
            'DISTINCT',
            'DISTRIBUTED',
            'DOUBLE',
            'DROP',
            'DUMP',
            'ELSE',
            'END',
            'ERRLVL',
            'ESCAPE',
            'EXCEPT',
            'EXEC',
            'EXECUTE',
            'EXISTS',
            'EXIT',
            'EXTERNAL',
            'FETCH',
            'FILE',
            'FILLFACTOR',
            'FOR',
            'FOREIGN',
            'FREETEXT',
            'FREETEXTTABLE',
            'FROM',
            'FULL',
            'FUNCTION',
            'GOTO',
            'GRANT',
            'GROUP',
            'HAVING',
            'HOLDLOCK',
            'IDENTITY',
            'IDENTITY_INSERT',
            'IDENTITYCOL',
            'IF',
            'IN',
            'INDEX',
            'INNER',
            'INSERT',
            'INTERSECT',
            'INTO',
            'IS',
            'JOIN',
            'KEY',
            'KILL',
            'LEFT',
            'LIKE',
            'LINENO',
            'LOAD',
            'MERGE',
            'NATIONAL',
            'NOCHECK',
            'NONCLUSTERED',
            'NOT',
            'NULL',
            'NULLIF',
            'OF',
            'OFF',
            'OFFSETS',
            'ON',
            'OPEN',
            'OPENDATASOURCE',
            'OPENQUERY',
            'OPENROWSET',
            'OPENXML',
            'OPTION',
            'OR',
            'ORDER',
            'OUTER',
            'OVER',
            'PERCENT',
            'PIVOT',
            'PLAN',
            'PRECISION',
            'PRIMARY',
            'PRINT',
            'PROC',
            'PROCEDURE',
            'PUBLIC',
            'RAISERROR',
            'READ',
            'READTEXT',
            'RECONFIGURE',
            'REFERENCES',
            'REPLICATION',
            'RESTORE',
            'RESTRICT',
            'RETURN',
            'REVERT',
            'REVOKE',
            'RIGHT',
            'ROLLBACK',
            'ROWCOUNT',
            'ROWGUIDCOL',
            'RULE',
            'SAVE',
            'SCHEMA',
            'SECURITYAUDIT',
            'SELECT',
            'SEMANTICKEYPHRASETABLE',
            'SEMANTICSIMILARITYDETAILSTABLE',
            'SEMANTICSIMILARITYTABLE',
            'SESSION_USER',
            'SET',
            'SETUSER',
            'SHUTDOWN',
            'SOME',
            'STATISTICS',
            'SYSTEM_USER',
            'TABLE',
            'TABLESAMPLE',
            'TEXTSIZE',
            'THEN',
            'TO',
            'TOP',
            'TRAN',
            'TRANSACTION',
            'TRIGGER',
            'TRUNCATE',
            'TRY_CONVERT',
            'TSEQUAL',
            'UNION',
            'UNIQUE',
            'UNPIVOT',
            'UPDATE',
            'UPDATETEXT',
            'USE',
            'USER',
            'VALUES',
            'VARYING',
            'VIEW',
            'WAITFOR',
            'WHEN',
            'WHERE',
            'WHILE',
            'WITH',
            'WRITETEXT',
        );
        $reserved_words = array_map('strtolower', $reserved_words);
        return $reserved_words;
    }

    /**
     * Does table with this fullname exist?
     *
     * Note that standard db prefix is not used here because
     * the test snapshots must use non-colliding table names.
     *
     * @param string $fulltablename
     * @return bool
     */
    private function general_table_exists($fulltablename) {
        return $this->mdb->record_exists_sql(
            "SELECT 'x'
               FROM INFORMATION_SCHEMA.TABLES
              WHERE table_name = ? AND table_type = 'BASE TABLE'", array($fulltablename));
    }

    /**
     * Store full database snapshot.
     */
    public function snapshot_create() {
        $this->mdb->transactions_forbidden();
        $prefix = $this->mdb->get_prefix();

        if (strpos('ss_', $prefix) === 0) {
            throw new coding_exception('Detected incorrect db prefix, cannot snapshot database due to potential data loss!');
        }

        if ($this->general_table_exists('ss_config')) {
            throw new coding_exception('Detected ss_config table, cannot snapshot database due to potential data loss!');
        }

        $sqls = array();
        $sqls[] = "IF OBJECT_ID ('ss_tables_{$prefix}', 'U') IS NOT NULL DROP TABLE ss_tables_{$prefix}";
        $sqls[] = "CREATE TABLE ss_tables_{$prefix} (
                      tablename VARCHAR(64) NOT NULL,
                      nextid INTEGER NOT NULL,
                      records INTEGER NOT NULL,
                      modifications INTEGER NOT NULL,
                      columnlist TEXT NOT NULL
                    )";
        $sqls[] = "CREATE UNIQUE INDEX ss_tables_{$prefix}_idx ON ss_tables_{$prefix} (tablename)";
        $this->mdb->change_database_structure($sqls, null);

        $sqls = array();
        $tables = $this->mdb->get_tables(false);
        foreach ($tables as $tablename => $unused) {
            $records = $this->mdb->count_records($tablename, array());
            $identity = $this->mdb->get_identity_info($tablename);
            if (!$identity) {
                $nextid = 0;
            } else {
                $nextid = $identity[2];
            }
            $columns = $this->mdb->get_records_sql("SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$prefix}{$tablename}'");
            $columnlist = implode(',', array_keys($columns));
            $sql = "INSERT INTO ss_tables_{$prefix} (tablename, nextid, records, modifications,columnlist) VALUES (?, ?, ?, 0, ?)";
            $this->mdb->execute($sql, array($prefix.$tablename, $nextid, $records, $columnlist));

            $sqls[] = "IF OBJECT_ID ('ss_t_{$prefix}{$tablename}', 'U') IS NOT NULL DROP TABLE ss_t_{$prefix}{$tablename}";
            if ($records > 0) {
                $sqls[] = "SELECT * INTO ss_t_{$prefix}{$tablename} FROM {$prefix}{$tablename}";
            }
            $sqls[] = "IF OBJECT_ID ('ss_trigger_{$prefix}{$tablename}', 'TR') IS NOT NULL DROP TRIGGER ss_trigger_{$prefix}{$tablename}";
            $sqls[] = "CREATE TRIGGER ss_trigger_{$prefix}{$tablename} ON {$prefix}{$tablename} AFTER INSERT, UPDATE, DELETE AS
                       BEGIN
                       SET NOCOUNT ON;
                       UPDATE ss_tables_{$prefix} SET modifications = 1 WHERE tablename = '{$prefix}{$tablename}' AND modifications = 0;
                       SET NOCOUNT OFF;
                       END;";
        }
        if ($sqls) {
            $this->mdb->change_database_structure($sqls, null);
        }
    }

    /**
     * Rollback the database to initial snapshot state.
     */
    public function snapshot_rollback() {
        $this->mdb->transactions_forbidden();
        $prefix = $this->mdb->get_prefix();

        $sqls = array();

        // Drop known temporary tables.
        $temptables = $this->temptables->get_temptables();
        foreach ($temptables as $temptable => $rubbish) {
            $this->temptables->delete_temptable($temptable);
            $sqls[] = "IF OBJECT_ID ('tempdb..#{$prefix}{$temptable}', 'U') IS NOT NULL DROP TABLE #{$prefix}{$temptable}";
        }

        // Reset modified tables.
        $infos = $this->mdb->get_records_sql("SELECT * FROM ss_tables_{$prefix} WHERE modifications = 1");
        foreach ($infos as $info) {
            $sqls[] = "TRUNCATE TABLE {$info->tablename}";
            if ($info->nextid > 0) {
                $sqls[] = "DBCC CHECKIDENT ('{$info->tablename}', RESEED, {$info->nextid})";
            }
            if ($info->records > 0) {
                $sqls[] = "SET IDENTITY_INSERT {$info->tablename} ON";
                $sqls[] = "INSERT INTO {$info->tablename} ({$info->columnlist}) SELECT {$info->columnlist} FROM ss_t_{$info->tablename}";
                $sqls[] = "SET IDENTITY_INSERT {$info->tablename} OFF";
            }
        }
        $sqls[] = "UPDATE ss_tables_{$prefix} SET modifications = 0 WHERE modifications = 1";

        // Delete extra tables.
        $escapedprefix = $this->mdb->sql_like_escape($prefix);
        $rs = $this->mdb->get_recordset_sql(
            "SELECT sch.table_name
               FROM INFORMATION_SCHEMA.TABLES sch
          LEFT JOIN ss_tables_{$prefix} ss ON ss.tablename = sch.table_name
              WHERE sch.table_name LIKE '{$escapedprefix}%' ESCAPE '\\' AND sch.table_type = 'BASE TABLE' AND ss.tablename IS NULL");
        foreach ($rs as $info) {
            $sqls[] = "DROP TABLE {$info->table_name}";
        }
        $rs->close();

        if ($sqls) {
            $this->mdb->change_database_structure($sqls);
        }
    }

    /**
     * Read config value from database snapshot.
     *
     * @param string $name
     * @return string|false the setting value or false if not found or snapshot missing
     */
    public function snapshot_get_config_value($name) {
        $prefix = $this->mdb->get_prefix();
        $configtable = "ss_t_{$prefix}config";

        if (!$this->general_table_exists($configtable)) {
            return false;
        }

        $sql = "SELECT value FROM {$configtable} WHERE name = ?";
        return $this->mdb->get_field_sql($sql, array($name));
    }

    /**
     * Remove all snapshot related database data and structures.
     */
    public function snapshot_drop() {
        $prefix = $this->mdb->get_prefix();
        $tablestable = "ss_tables_{$prefix}";
        if (!$this->general_table_exists($tablestable)) {
            return;
        }

        $sqls = array();
        $rs = $this->mdb->get_recordset_sql("SELECT * FROM ss_tables_{$prefix}");
        foreach ($rs as $info) {
            $sqls[] = "IF OBJECT_ID ('ss_trigger_{$info->tablename}', 'TR') IS NOT NULL DROP TRIGGER ss_trigger_{$info->tablename}";
            $sqls[] = "IF OBJECT_ID ('ss_t_{$info->tablename}', 'U') IS NOT NULL DROP TABLE ss_t_{$info->tablename}";
        }
        $rs->close();
        $sqls[] = "IF OBJECT_ID ('{$tablestable}', 'U') IS NOT NULL DROP TABLE {$tablestable}";

        $this->mdb->change_database_structure($sqls);
    }
}
