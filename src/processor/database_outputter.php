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
 * Contains the {@link DatabaseOutputter} class
 *
 * @package Outputters
 * @author Josh
 * @since 0.2
 **/

/**
 * Outputs the tree to a database.
 **/
abstract class DatabaseOutputter extends Outputter {
    static private $since_versions;
    protected $extra_insert_data = array();

    /**
     * Connects to the database
     **/
    abstract protected function connect();

    /**
     * Executes a database query
     */
    abstract protected function query($query);

    /**
     * Safens some input
     * @param string $input The input to safen
     **/
    abstract protected function sql_safen($input);


    /**
     * Fetches a row from the database (numerical)
     **/
    abstract protected function fetch_row($res);

    /**
     * Fetches a row from the database (assoc)
     **/
    abstract protected function fetch_assoc($res);

    /**
     * Returns the number of rows affected in the last query
     **/
    abstract protected function affected_rows($res);

    /**
     * Returns the autogenerated id created in the last query
     **/
    abstract protected function insert_id();


    /**
     * Returns an array of the tables in this database
     **/
    abstract protected function get_table_list();

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
    abstract protected function get_column_details($table_name);

    /**
     * Gets the query that alters a column to match the new SQL definition
     **/
    abstract protected function get_alter_column_query($table, $column_name, $new_type, $null_allowed);

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
    abstract protected function get_sql_type($internal_type_name);

    /**
     * Creates a table
     **/
    abstract protected function create_table($table_name, $columns);

    /**
     * The database engine should start a transaction. If transactions are not supported, it should do nothing.
     **/
    abstract protected function start_transaction();

    /**
     * The database engine should commit a transaction. If transactions are not supported, it should do nothing.
     **/
    abstract protected function commit_transaction();

    /**
     * The database engine should rollback a transaction. If transactions are not supported, it should do nothing.
     **/
    abstract protected function rollback_transaction();


    /**
     * Executes an insert query for the data provided.
     **/
    private function do_insert($table, $data)
    {
        $data += $this->extra_insert_data;
        $q = "INSERT INTO {$table} (";
        $q .= implode(', ', array_keys($data));
        $q .= ") VALUES (";
        $q .= implode(',', $data);
        $q .= ")";
        $this->query($q);
    }


    /**
     * Executes an insert query for the data provided.
     **/
    private function do_multiple_insert($table, $data)
    {
        if (count($data) == 0) return;

        $q = "INSERT INTO {$table} (";
        $q .= implode(',', array_keys($data[0] + $this->extra_insert_data));
        $q .= ") VALUES ";

        $j = 0;
        foreach ($data as $row) {
            if ($j++ > 0) $q .= ',';
            $q .= '(' . implode(',', $row + $this->extra_insert_data) . ')';
        }

        $this->query($q);
    }


    /**
     * Executes an update query for the data provided.
     **/
    private function do_update($table, $data, $where)
    {
        $q = "UPDATE {$table} SET ";
        $j = 0;
        foreach ($data as $key => $val) {
            if ($j++) $q .= ',';
            $q .= $key . ' = ' . $val;
        }
        $q .= ' WHERE ';
        $j = 0;
        foreach ($where as $key => $val) {
            if ($j++) $q .= ',';
            $q .= $key . ' = ' . $val;
        }
        $this->query($q);
    }


    /**
     * Updates the database layout to match the layout file
     * NOTE: currently only supports column and table adding and updating, not removal.
     *
     * @param string $layout_filename The name of hte layout file to match
     **/
    public function check_layout($layout_filename)
    {
        $layout_lines = file($layout_filename);

        $res = $this->connect();
        if (! $res) {
            echo "<p>Unable to connect to database!";
            return false;
        }

        $dest_tables = array ();
        $table = null;

        foreach ($layout_lines as $line) {
            $line = trim($line);
            if ($line == '') continue;

            $words = explode(' ', $line, 3);

            switch ($words[0]) {
            case 'TABLE':
                $table = $words[1];
                break;

            case 'PK':
                $dest_tables[$table]['PK'] = $words[1];
                break;

            default:
                $col = array();
                $col['Type'] = $words[1];

                $col['NotNull'] = 0;
                if (isset($words[2])) {
                    if ($words[2] == 'notnull') $col['NotNull'] = 1;
                    if ($words[2] == 'null') $col['NotNull'] = 0;
                }

                $dest_tables[$table]['Columns'][$words[0]] = $col;
                break;
            }
        }


        $curr_tables = array ();

        $table_names = $this->get_table_list();
        foreach ($table_names as $table_name) {
            $curr_tables[$table_name] = array();

            $colres = $this->get_column_details($table_name);

            foreach ($colres as $colrow) {
                $colrow['Type'] = strtolower($colrow['Type']);

                if ($colrow['Key'] == 'PRI') {
                    $curr_tables[$table_name]['PK'] = $colrow['Field'];
                }
                unset ($colrow['Key']);

                $curr_tables[$table_name]['Columns'][$colrow['Field']] = $colrow;
            }
        }



        foreach ($dest_tables as $table_name => $dest_table) {
            $curr_table = $curr_tables[$table_name];

            if ($curr_table === null) {
                // Create the table if it does not yet exist.
                echo "Create table {$table_name}.\n";

                $this->create_table($table_name, $dest_table);


            } else {
                echo "Altering table {$table_name}\n";

                // Update PK
                if ($curr_table['PK'] != $dest_table['PK']) {
                    echo "  Change primary key from {$curr_table['PK']} to {$dest_table['PK']}\n";
                    /* not yet supported */
                }

                // Update columns
                foreach ($dest_table['Columns'] as $column_name => $dest_column) {
                    $curr_column = $curr_table['Columns'][$column_name];

                    $dest_sql = $this->get_sql_type($dest_column['Type']);
                    if ($dest_column['NotNull']) $dest_sql .= ' not null';

                    if ($curr_column == null) {
                        echo "  Create column {$column_name}. New def: '{$dest_sql}'\n";

                        $q = "ALTER TABLE {$table_name} ADD COLUMN {$column_name} {$dest_sql}";
                        echo "    <b>Query: {$q}</b>\n";

                        $res = $this->query ($q);
                        if ($res) echo '    Affected rows: ', $this->affected_rows($res), "\n";


                    } else {
                        $curr_sql = $this->get_sql_type($curr_column['Type']);
                        if ($curr_column['NotNull']) $curr_sql .= ' not null';

                        if ($curr_sql != $dest_sql) {
                            echo "  Update col {$column_name}. Old def: '{$curr_sql}' New def: '{$dest_sql}'\n";

                            $q = $this->get_alter_column_query ($table_name, $column_name, $curr_column['Type'], $curr_column['NotNull']);
                            echo "    <b>Query: {$q}</b>\n";

                            $res = $this->query ($q);
                            if ($res) echo '    Affected rows: ', $this->affected_rows($res), "\n";

                        } else {
                            echo "  Column {$column_name} does not need to be changed\n";
                        }
                    }

                }
            }

            echo "\n";
        }
    }


    /**
     * Adds a @since version to from a {@link CodeParserItem} to the internal list
     * This list is used to fill a table with all of the versions of the program in existance
     **/
    static function addSinceVersion(CodeParserItem $parser_item, $parent)
    {
        if ($parser_item->since == '') return;

        if (! in_array($parser_item->since, self::$since_versions)) {
            self::$since_versions[] = $parser_item->since;
        }
    }


    /**
     * Gets the database id of a record for a specific @since version
     **/
    private function getSinceVersionId($since_version)
    {
        $res = array_search($since_version, self::$since_versions);
        if ($res === false) return null;
        return $res + 1;
    }


    /**
     * Does the actual outputting of the file objects (and theihttps://www.nationalcrimecheck.com.au/r sub-objects) to the database
     *
     * @param array $files The file objects to save to the database
     * @param Config $config The project config
     *
     * @table insert projects The main project record
     * @table insert packages All of the packages used by this project
     * @table insert versions All of the project versions documented
     * @table insert files All of the files
     * @table insert documents All of the documents
     **/
    public function output($files, Config $config)
    {
        $res = $this->connect();
        if (! $res) {
            echo "<p>Unable to connect to database!";
            return false;
        }

        // Get existing or create new project
        $code = $this->sql_safen($config->getProjectCode());
        $res = $this->query("SELECT id FROM projects WHERE code = {$code}");
        $row = $this->fetch_assoc($res);
        if ($row) {
            $project_id = $row['id'];
        } else {
            $insert_data = array();
            $insert_data['code'] = $this->sql_safen($config->getProjectCode());
            $this->do_insert('projects', $insert_data);
            $project_id = $this->insert_id();
        }

        // Update project details
        $update_data = array();
        $update_data['name'] = $this->sql_safen($config->getProjectName());
        $update_data['license'] = $this->sql_safen($config->getLicenseText());
        $update_data['dategenerated'] = $this->sql_safen(date('Y-m-d h:i a'));
        $this->do_update('projects', $update_data, array('id' => $project_id));

        // Include project id in all inserts
        $this->extra_insert_data = array(
            'projectid' => $project_id,
        );

        // Only delete data from this project
        $this->query("DELETE FROM files WHERE projectid = {$project_id}");
        $this->query("DELETE FROM functions WHERE projectid = {$project_id}");
        $this->query("DELETE FROM arguments WHERE projectid = {$project_id}");
        $this->query("DELETE FROM classes WHERE projectid = {$project_id}");
        $this->query("DELETE FROM class_implements WHERE projectid = {$project_id}");
        $this->query("DELETE FROM packages WHERE projectid = {$project_id}");
        $this->query("DELETE FROM interfaces WHERE projectid = {$project_id}");
        $this->query("DELETE FROM variables WHERE projectid = {$project_id}");
        $this->query("DELETE FROM constants WHERE projectid = {$project_id}");
        $this->query("DELETE FROM item_authors WHERE projectid = {$project_id}");
        $this->query("DELETE FROM item_tables WHERE projectid = {$project_id}");
        $this->query("DELETE FROM documents WHERE projectid = {$project_id}");
        $this->query("DELETE FROM versions WHERE projectid = {$project_id}");
        $this->query("DELETE FROM item_see WHERE projectid = {$project_id}");
        $this->query("DELETE FROM enumerations WHERE projectid = {$project_id}");
        $this->query("DELETE FROM item_info_tags WHERE projectid = {$project_id}");

        // get all of the unique package names, and create packages
        $packages = array();
        foreach ($files as $file) {
            if (!empty($file->package)) {
                if (! isset($packages[$file->package])) {
                    $insert_data = array();
                    $insert_data['name'] = $this->sql_safen ($file->package);
                    $this->do_insert('packages', $insert_data);
                    $packages[$file->package] = $this->insert_id();
                }

            } else {
                $needs_default_package = true;
            }
        }

        if ($needs_default_package) {
            $insert_data = array();
            $insert_data['name'] = $this->sql_safen ('Default');
            $this->do_insert('packages', $insert_data);
            $default_id = $this->insert_id();
        }

        // Determine the versions that are available
        self::$since_versions = array();
        foreach ($files as $item) {
            if ($item instanceof ParserFile) {
                $item->treeWalk(array('DatabaseOutputter', 'addSinceVersion'));
            }
        }

        // Sorts the versions array
        natsort(self::$since_versions);
        self::$since_versions = array_reverse(self::$since_versions);

        // And add them to the table
        foreach (self::$since_versions as $version) {
            $insert_data = array();
            $insert_data['name'] = $this->sql_safen($version);
            $this->do_insert('versions', $insert_data);
        }

        // go through all the files
        foreach ($files as $item) {
            if ($item instanceof ParserFile) {
                // Inserts a file
                $package = @$packages[$item->package];
                if ($package == null) $package = $default_id;
                $package = $this->sql_safen($package);

                $insert_data = array();
                $insert_data['name'] = $this->sql_safen($item->name);
                $insert_data['description'] = $this->sql_safen($item->description);
                $insert_data['source'] = $this->sql_safen($item->source);
                $insert_data['sinceid'] = $this->sql_safen($this->getSinceVersionId($item->since));
                $insert_data['packageid'] = $package;

                $this->do_insert('files', $insert_data);
                $file_id = $this->insert_id ();

                // this files functions
                foreach ($item->functions as $function) {
                    $this->save_function ($function, $file_id);
                }

                // this files classes
                foreach ($item->classes as $class) {
                    if ($class instanceof ParserClass) {
                        $this->save_class ($class, $file_id);
                    } else if ($class instanceof ParserInterface) {
                        $this->save_interface ($class, $file_id);
                    }
                }

                // this files constants
                foreach ($item->constants as $constant) {
                    $this->save_constant($constant, $file_id);
                }

                // this files enums
                foreach ($item->enumerations as $enumeration) {
                    $this->save_enumeration($enumeration, $file_id);
                }

                // Common items
                $this->save_author_items (LINK_TYPE_FILE, $file_id, $item->authors);
                $this->save_table_items (LINK_TYPE_FILE, $file_id, $item->tables);
                $this->save_see_items (LINK_TYPE_FILE, $file_id, $item->see);
                $this->save_info_tag_items (LINK_TYPE_FILE, $file_id, $item->info_tags);


            } else if ($item instanceof ParserDocument) {
                // Inserts a document
                $insert_data = array();
                $insert_data['name'] = $this->sql_safen($item->name);
                $insert_data['description'] = $this->sql_safen($item->description);

                $this->do_insert('documents', $insert_data);


            }
        }

        //$this->commit_transaction();

        return true;
    }


    /**
     * Saves a function to the database
     *
     * @table insert functions Adds the function details
     * @table insert arguments Adds the arguments for this function
     **/
    private function save_function($function, $file_id, $class_id = null, $interface_id = null)
    {
        // Ignore closures
        if ($function->name == null) return;

        // prepare data for inserting
        $insert_data = array();
        $insert_data['static'] = 0;
        $insert_data['final'] = 0;
        $insert_data['name'] = $this->sql_safen($function->name);
        $insert_data['description'] = $this->sql_safen($function->description);
        $insert_data['fileid'] = $file_id;
        $insert_data['sinceid'] = $this->sql_safen($this->getSinceVersionId($function->since));

        // Class-specific details
        if ($class_id != null) {
            $insert_data['classid'] = $class_id;
            $insert_data['visibility'] = $this->sql_safen($function->visibility);

            // Interface-specific details
        } else if ($interface_id != null) {
            $insert_data['interfaceid'] = $interface_id;
            $insert_data['visibility'] = $this->sql_safen($function->visibility);
        }

        // Return value
        if ($function->return_type != null) {
            $insert_data['returntype'] = $this->sql_safen($function->return_type);
            $insert_data['returndescription'] = $this->sql_safen($function->return_description);
        }

        if ($function->static) $insert_data['static'] = 1;
        if ($function->final) $insert_data['final'] = 1;

        // build arguments string
        if (count($function->args) > 0) {
            $args = array();
            foreach ($function->args as $arg) {
                if ($arg->type != null) {
                    $args[] = $arg->type . ' ' . $arg->name;
                } else {
                    $args[] = $arg->name;
                }
            }
            $insert_data['arguments'] = $this->sql_safen(implode(', ', $args));

        } else {
            $insert_data['arguments'] = "''";
        }

        // build query from prepared data
        $this->do_insert('functions', $insert_data);
        $function_id = $this->insert_id ();


        // Insert common items
        $this->save_author_items (LINK_TYPE_FUNCTION, $function_id, $function->authors);
        $this->save_table_items (LINK_TYPE_FUNCTION, $function_id, $function->tables);
        $this->save_see_items (LINK_TYPE_FUNCTION, $function_id, $function->see);
        $this->save_info_tag_items (LINK_TYPE_FUNCTION, $function_id, $function->info_tags);

        // insert arguments
        $args = array();
        foreach ($function->args as $arg) {
            $insert_data = array();
            $insert_data['name'] = $this->sql_safen($arg->name);
            $insert_data['type'] = $this->sql_safen($arg->type);
            $insert_data['defaultvalue'] = $this->sql_safen($arg->default);
            $insert_data['description'] = $this->sql_safen($arg->description);
            $insert_data['functionid'] = $this->sql_safen($function_id);
            $args[] = $insert_data;
        }

        // insert return value
        if (isset($function->return)) {
            $insert_data = array();
            $insert_data['name'] = $this->sql_safen('__RETURN__');
            $insert_data['type'] = $this->sql_safen($function->return->type);
            $insert_data['description'] = $this->sql_safen($function->return->description);
            $insert_data['functionid'] = $this->sql_safen($function_id);
            $args[] = $insert_data;
        }

        $this->do_multiple_insert('arguments', $args);
    }


    /**
     * Saves a class to the database
     *
     * @table insert classes Adds the class information for this class
     * @table insert class_implements Adds the interfaces that this class extends
     **/
    private function save_class($class, $file_id)
    {
        // prepare the data for inserting
        $insert_data = array();
        $insert_data['abstract'] = 0;
        $insert_data['final'] = 0;
        $insert_data['name'] = $this->sql_safen($class->name);
        $insert_data['description'] = $this->sql_safen($class->description);
        $insert_data['extends'] = $this->sql_safen($class->extends);
        $insert_data['visibility'] = $this->sql_safen($class->visibility);
        $insert_data['fileid'] = $file_id;
        $insert_data['sinceid'] = $this->sql_safen($this->getSinceVersionId($class->since));

        if ($class->abstract) $insert_data['abstract'] = 1;
        if ($class->final) $insert_data['final'] = 1;

        // Build and process query from prepared data
        $this->do_insert('classes', $insert_data);
        $class_id = $this->insert_id ();

        // process implements
        foreach ($class->implements as $implements) {
            $insert_data = array();
            $insert_data['classid'] = $class_id;
            $insert_data['name'] = $this->sql_safen($implements);

            $this->do_insert('class_implements', $insert_data);
        }


        // process functions
        foreach ($class->functions as $function) {
            $this->save_function($function, $file_id, $class_id);
        }

        // process variables
        foreach ($class->variables as $variable) {
            $this->save_variable($variable, $class_id);
        }

        // Insert common items
        $this->save_author_items (LINK_TYPE_CLASS, $class_id, $class->authors);
        $this->save_table_items (LINK_TYPE_CLASS, $class_id, $class->tables);
        $this->save_see_items (LINK_TYPE_CLASS, $class_id, $class->see);
        $this->save_info_tag_items (LINK_TYPE_CLASS, $class_id, $class->info_tags);
    }


    /**
     * Saves an interface to the database
     *
     * @table insert interfaces
     **/
    private function save_interface($interface, $file_id)
    {
        // prepare the data for inserting
        $insert_data = array();
        $insert_data['name'] = $this->sql_safen($interface->name);
        $insert_data['description'] = $this->sql_safen($interface->description);
        $insert_data['extends'] = $this->sql_safen($interface->extends);
        $insert_data['visibility'] = $this->sql_safen($interface->visibility);
        $insert_data['fileid'] = $file_id;
        $insert_data['sinceid'] = $this->sql_safen($this->getSinceVersionId($interface->since));


        // Build and process query from prepared data
        $this->do_insert('interfaces', $insert_data);
        $interface_id = $this->insert_id ();


        // process functions
        foreach ($interface->functions as $function) {
            $this->save_function ($function, $file_id, null, $interface_id);
        }

        // insert common items
        $this->save_author_items (LINK_TYPE_INTERFACE, $interface_id, $interface->authors);
        $this->save_see_items (LINK_TYPE_INTERFACE, $interface_id, $interface->see);
        $this->save_info_tag_items (LINK_TYPE_INTERFACE, $interface_id, $interface->info_tags);
    }


    /**
     * Saves a variable to the database
     *
     * @table insert variables
     **/
    private function save_variable($variable, $class_id = null, $interface_id = null)
    {
        // prepare data for inserting
        $insert_data = array();
        $insert_data['static'] = 0;
        $insert_data['name'] = $this->sql_safen($variable->name);
        $insert_data['description'] = $this->sql_safen($variable->description);
        //$insert_data['visibility'] = $this->sql_safen($variable->visibility);
        $insert_data['sinceid'] = $this->sql_safen($this->getSinceVersionId($variable->since));


        // Class-specific details
        if ($class_id != null) {
            $insert_data['classid'] = $class_id;

            // Interface-specific details
        } else if ($interface_id != null) {
            $insert_data['interfaceid'] = $interface_id;
        }

        if ($variable->static) $insert_data['static'] = 1;


        // Build and process query from prepared data
        $this->do_insert('variables', $insert_data);
        $variable_id = $this->insert_id ();

        // insert common items
        $this->save_author_items (LINK_TYPE_VARIABLE, $variable_id, $variable->authors);
        $this->save_see_items (LINK_TYPE_VARIABLE, $variable_id, $variable->see);
        $this->save_info_tag_items (LINK_TYPE_VARIABLE, $variable_id, $variable->info_tags);
    }


    /**
     * Saves a constant to the database
     *
     * @table insert constants
     **/
    private function save_constant($constant, $file_id = null, $enumeration_id = null)
    {
        // prepare data for inserting
        $insert_data = array();
        $insert_data['name'] = $this->sql_safen($constant->name);
        $insert_data['value'] = $this->sql_safen($constant->value);
        $insert_data['description'] = $this->sql_safen($constant->description);
        $insert_data['fileid'] = $this->sql_safen($file_id);
        $insert_data['sinceid'] = $this->sql_safen($this->getSinceVersionId($constant->since));

        if ($enumeration_id != null) {
            $insert_data['enumerationid'] = $enumeration_id;
        }

        // Build and process query from prepared data
        $this->do_insert('constants', $insert_data);
        $constant_id = $this->insert_id ();

        // insert common items
        $this->save_author_items (LINK_TYPE_CONSTANT, $constant_id, $constant->authors);
        $this->save_see_items (LINK_TYPE_CONSTANT, $constant_id, $constant->see);
        $this->save_info_tag_items (LINK_TYPE_CONSTANT, $constant_id, $constant->info_tags);
    }


    /**
     * Saves a enumeration to the database
     *
     * @table insert enumerations
     **/
    private function save_enumeration($enumeration, $file_id = null)
    {
        // prepare data for inserting
        $insert_data = array();
        $insert_data['name'] = $this->sql_safen($enumeration->name);
        $insert_data['description'] = $this->sql_safen($enumeration->description);
        $insert_data['fileid'] = $this->sql_safen($file_id);
        $insert_data['sinceid'] = $this->sql_safen($this->getSinceVersionId($constant->since));
        $insert_data['virtual'] = $this->sql_safen($enumeration->virtual);

        // Build and process query from prepared data
        $this->do_insert('enumerations', $insert_data);
        $enumeration_id = $this->insert_id ();

        // insert common items
        $this->save_author_items (LINK_TYPE_ENUMERATION, $enumeration_id, $enumeration->authors);
        $this->save_see_items (LINK_TYPE_ENUMERATION, $enumeration_id, $enumeration->see);
        $this->save_info_tag_items (LINK_TYPE_ENUMERATION, $enumeration_id, $enumeration->info_tags);

        // Save the constants for this enumeration
        foreach ($enumeration->constants as $constant) {
            $this->save_constant ($constant, $file_id, $enumeration_id);
        }
    }


    /**
     * Saves author information about an item
     *
     * @table insert item_authors
     **/
    private function save_author_items($link_type, $link_id, $items)
    {
        $rows = array();
        foreach ($items as $item) {
            $insert_data = array();
            $insert_data['linkid'] = $this->sql_safen($link_id);
            $insert_data['linktype'] = $this->sql_safen($link_type);
            $insert_data['name'] = $this->sql_safen($item->name);
            $insert_data['email'] = $this->sql_safen($item->email);
            $insert_data['description'] = $this->sql_safen($item->description);
            $rows[] = $insert_data;
        }
        $this->do_multiple_insert('item_authors', $rows);
    }


    /**
     * Saves table usage information about an item
     *
     * @since 0.2
     * @table insert item_tables Adds information about the tables that are used by a function, class or file.
     **/
    private function save_table_items($link_type, $link_id, $items)
    {
        $rows = array();
        foreach ($items as $item) {
            $insert_data = array();
            $insert_data['linkid'] = $this->sql_safen($link_id);
            $insert_data['linktype'] = $this->sql_safen($link_type);
            $insert_data['name'] = $this->sql_safen($item->name);
            $insert_data['action'] = $this->sql_safen($item->action);
            $insert_data['description'] = $this->sql_safen($item->description);
            $rows[] = $insert_data;
        }
        $this->do_multiple_insert('item_tables', $rows);
    }


    /**
     * Saves 'see also' information about an item
     *
     * @since 0.2
     * @table insert item_see Adds 'see also' links for a function, class, file, etc.
     **/
    private function save_see_items($link_type, $link_id, $items)
    {
        $rows = array();
        foreach ($items as $item) {
            $insert_data = array();
            $insert_data['linkid'] = $this->sql_safen($link_id);
            $insert_data['linktype'] = $this->sql_safen($link_type);
            $insert_data['name'] = $this->sql_safen($item);
            $rows[] = $insert_data;
        }
        $this->do_multiple_insert('item_see', $rows);
    }


    /**
     * Saves info tags for an item
     *
     * @since 0.3
     * @table insert item_info_tags Adds info tags links for a function, class, file, etc.
     **/
    private function save_info_tag_items($link_type, $link_id, $items)
    {
        $rows = array();
        foreach ($items as $item) {
            $insert_data = array();
            $insert_data['linkid'] = $this->sql_safen($link_id);
            $insert_data['linktype'] = $this->sql_safen($link_type);
            $insert_data['name'] = $this->sql_safen($item);
            $rows[] = $insert_data;
        }
        $this->do_multiple_insert('item_info_tags', $rows);
    }


}


?>
