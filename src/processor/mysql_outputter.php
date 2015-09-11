<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of Pelzini.

Pelzini is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Pelzini is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Pelzini.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * Contains the {@link MysqlOutputter} class
 *
 * @package Outputters
 * @author Josh
 * @since 0.1
 **/

/**
 * Outputs the tree as MySQL
 **/
class MysqlOutputter extends DatabaseOutputter {
    private $username;
    private $password;
    private $server;
    private $database;
    private $db;

    /**
     * Connects to the db
     */
    public function __construct($username, $password, $server, $database)
    {
        $this->username = $username;
        $this->password = $password;
        $this->server = $server;
        $this->database = $database;
    }


    /**
     * Closes connection to the db
     */
    public function __destruct()
    {
        if ($this->db) mysql_close($this->db);
    }



    /**
     * Connects to the MySQL database
     **/
    protected function connect()
    {
        $this->db = @mysql_connect($this->server, $this->username, $this->password);
        if ($this->db == false) return false;
        mysql_select_db($this->database, $this->db);
        return true;
    }


    /**
     * Executes a MySQL query
     */
    protected function query($query)
    {
        $return = mysql_query($query, $this->db);
        if ($return === false) {
            echo "<p>Error in query <em>{$query}</em>. MySQL reported the following: <em>" . mysql_error() . "</em></p>";
        }
        return $return;
    }


    /**
     * Safens some input
     * @param string $input The input to safen
     **/
    protected function sql_safen($input)
    {
        if ($input === null) {
            return 'NULL';
        } else if (is_integer($input)) {
            return $input;
        } else {
            return "'" . mysql_real_escape_string($input, $this->db) . "'";
        }
    }


    /**
     * Fetches a row from the database (numerical)
     **/
    protected function fetch_row($res)
    {
        return mysql_fetch_row($res);
    }


    /**
     * Fetches a row from the database (assoc)
     **/
    protected function fetch_assoc($res)
    {
        return mysql_fetch_assoc($res);
    }


    /**
     * Returns the number of rows affected in the last query
     **/
    protected function affected_rows($res)
    {
        return mysql_affected_rows();
    }


    /**
     * Returns the autogenerated id created in the last query
     **/
    protected function insert_id()
    {
        return mysql_insert_id();
    }


    /**
     * Returns an array of the tables in this database
     **/
    protected function get_table_list()
    {
        $q = "SHOW TABLES";
        $res = $this->query ($q);

        $tables = array();
        while ($row = $this->fetch_row($res)) {
            $tables[] = $row[0];
        }

        return $tables;
    }


    /**
     * Converts an internal type into the database-specific SQL type.
     * The defined internal types are:
     *   - serial: a number that automatically increments whenever a record is added
     *   - smallnum: a small number. needs to be able to hold at least 32,767 possible values (e.g. a 16-bit signed integer)
     *   - largenum: a large number. needs to be the same size or larger than a serial type
     *   - string: a character field long enough to hold identifiers of objects (e.g. function names)
     *   - text: a field that can hold arbitary pieces of text larger than 65536 chars in length.
     *
     * @param string $internal_type_name The internal type name.
     * @return string The name used by the SQL database.
     **/
    protected function get_sql_type($internal_type_name)
    {
        switch ($internal_type_name) {
        case 'serial': return 'SERIAL';
        case 'smallnum': return 'SMALLINT UNSIGNED';
        case 'largenum': return 'BIGINT UNSIGNED';
        case 'string': return 'VARCHAR(255)';
        case 'text': return 'MEDIUMTEXT';
        default:
            throw new Exception ("Undefined type '{$internal_type_name}' specified");
            break;
        }
    }


    /**
     * Should return a multi-dimentional array of the column details
     * Format:
     * Array [
     *   [0] => Array [
     *      'Field' => field name
     *      'Type' => field type, (e.g. 'serial', 'smallnum' or 'identifier')
     *      'NotNull' => nullable?, (true or false)
     *      'Key' => indexed?, ('PRI' for primary key)
     *      ]
     *    [1] => ...
     *    [n] => ...
     **/
    protected function get_column_details($table_name)
    {
        $q = 'SHOW COLUMNS IN ' . $table_name;
        $res = $this->query ($q);

        $columns = array();
        while ($row = $this->fetch_assoc($res)) {
            if ($row['Null'] == 'YES') {
                $row['NotNull'] = false;
            } else {
                $row['NotNull'] = true;
            }

            // Remap the SQL types back to Pelzini type
            $row['Type'] = preg_replace('/\(.+\)/', '', $row['Type']);
            $row['Type'] = strtolower($row['Type']);
            switch ($row['Type']) {
            case 'smallint unsigned': $row['Type'] = 'smallnum'; break;
            case 'smallint': $row['Type'] = 'smallnum'; break;
            case 'bigint unsigned':
                $row['Type'] = 'largenum';
                if ($row['NotNull'] and stripos('auto_increment', $row['Extra']) !== false) {
                    $row['Type'] = 'serial';
                }
                break;
            case 'bigint': $row['Type'] = 'largenum'; break;
            case 'int unsigned': $row['Type'] = 'largenum'; break;
            case 'int': $row['Type'] = 'largenum'; break;
            case 'varchar': $row['Type'] = 'string'; break;
            case 'mediumtext': $row['Type'] = 'text'; break;
            }

            unset ($row['Extra'], $row['Default']);
            $columns[] = $row;
        }

        return $columns;
    }


    /**
     * Should return a multi-dimentional array of the index details
     * Format:
     * Array [
     *   [0] => Array [
     *      'Fields' => array of field names
     *      ]
     *   [1] => ...
     *   [n] => ...
     **/
    protected function get_index_details($table_name)
    {
        $q = 'SHOW INDEXES IN ' . $table_name;
        $res = $this->query ($q);

        $indexes = array();
        while ($row = $this->fetch_assoc($res)) {
            if (!isset($indexes[$row['Key_name']])) {
                $indexes[$row['Key_name']] = array('Fields' => array());
            }

            $indexes[$row['Key_name']]['Fields'][] = $row['Column_name'];
        }

        return $indexes;
    }


    /**
     * Gets the query that alters a column to match the new SQL definition
     **/
    protected function get_alter_column_query($table, $column_name, $new_type, $not_null)
    {
        $new_type = $this->get_sql_type($new_type);

        $q = "ALTER TABLE {$table} MODIFY COLUMN {$column_name} {$new_type}";
        return $q;
    }


    /**
     * Creates a table
     **/
    protected function create_table($table_name, $dest_table)
    {
        $q = "CREATE TABLE {$table_name} (\n";
        foreach ($dest_table['Columns'] as $col_name => $col_def) {
            $dest_sql = $this->get_sql_type($col_def['Type']);
            if ($col_def['NotNull']) $dest_sql .= ' not null';

            $q .= "  {$col_name} {$dest_sql},\n";
        }
        foreach ($dest_table['Indexes'] as $col_name) {
            $q .= "  INDEX ({$col_name}),\n";
        }
        $q .= "  PRIMARY KEY ({$dest_table['PK']})\n";
        $q .= ") ENGINE=MyISAM";
        echo "<b>Query:\n{$q}</b>\n";

        $res = $this->query ($q);
        if ($res) echo 'Affected rows: ', $this->affected_rows($res), "\n";
    }


    /**
     * The database engine should start a transaction. If transactions are not supported, it should do nothing.
     **/
    protected function start_transaction()
    {
        $this->query('START TRANSACTION');
    }


    /**
     * The database engine should commit a transaction. If transactions are not supported, it should do nothing.
     **/
    protected function commit_transaction()
    {
        $this->query('COMMIT');
    }


    /**
     * The database engine should rollback a transaction. If transactions are not supported, it should do nothing.
     **/
    protected function rollback_transaction()
    {
        $this->query('ROLLBACK');
    }


}


?>
