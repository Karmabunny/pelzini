<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of docu.

Docu is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Docu is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with docu.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
* @package Outputters
* @author Josh
* @since 0.2
**/

/**
* Outputs the tree to a database
**/
abstract class DatabaseOutputter extends Outputter {
  
  /**
  * Connects to the database
  **/
  abstract protected function connect ();
  
  /**
  * Executes a database query
  */
  abstract protected function query ($query);

  /**
  * Safens some input
  * @param string $input The input to safen
  **/
  abstract protected function sql_safen ($input);
  
  
  /**
  * Fetches a row from the database (numerical)
  **/
  abstract protected function fetch_row ($res);
  
  /**
  * Fetches a row from the database (assoc)
  **/
  abstract protected function fetch_assoc ($res);
  
  /**
  * Returns the number of rows affected in the last query
  **/
  abstract protected function affected_rows ($res);
  
  /**
  * Returns the autogenerated id created in the last query
  **/
  abstract protected function insert_id ();
  
  
  /**
  * Returns an array of the tables in this database
  **/
  abstract protected function get_table_list ();
  
  /**
  * Should return a multi-dimentional array of the column details
  * Format:
  * Array [
  *   [0] => Array [
  *      'Field' => field name
  *      'Type' => field type, (e.g. 'int unsigned' or 'varchar(255)')
  *      'Null' => nullable?, (e.g. 'NO' or 'YES')
  *      'Key' => indexed?, ('PRI' for primary key)
  *      'Extra' => extra info, (to contain 'auto_increment' if an auto-inc column)
  *      ]
  *    [1] => ...
  *    [n] => ...
  **/
  abstract protected function get_column_details ($table_name);
  
  
  /**
  * Creates an insert query from the data provided.
  * The data should be key-value pairs of the field name => escaped field value
  **/
  private function create_insert_query ($table, $data) {
    $q = "INSERT INTO {$table} (";
    $q .= implode (', ', array_keys ($data));
    $q .= ") VALUES (";
    $q .= implode (', ', $data);
    $q .= ")";
    
    return $q;
  }
  
  
  /**
  * Updates the database layout to match the layout file
  * NOTE: currently only supports column and table adding and updating, not removal.
  * 
  * @param string $layout_filename The name of hte layout file to match
  **/
  public function check_layout ($layout_filename) {
    $layout_lines = file ($layout_filename);
    
    $res = $this->connect();
    if (! $res) {
      echo "<p>Unable to connect to database!";
      return false;
    }
    
    $dest_tables = array ();
    $table = null;
    
    foreach ($layout_lines as $line) {
      $line = trim ($line);
      if ($line == '') continue;
      
      $words = explode (' ', $line, 2);
      
      switch ($words[0]) {
        case 'TABLE':
          $table = $words[1];
          break;
          
        case 'PK':
          $dest_tables[$table]['PK'] = $words[1];
          break;
          
        default:
          $dest_tables[$table]['Columns'][$words[0]] = $words[1];
          break;
      }
    }
    
    
    $curr_tables = array ();
    
    $table_names = $this->get_table_list();
    foreach ($table_names as $table_name) {
      $curr_tables[$table_name] = array();
      
      $colres = $this->get_column_details($table_name);
      
      foreach ($colres as $colrow) {
        $def = $colrow['Type'];
        if (! $colrow['Null']) $def .= ' not null';
        if ($colrow['Extra']) $def .= ' ' . $colrow['Extra'];
        
        $curr_tables[$table_name]['Columns'][$colrow['Field']] = strtolower($def);
        
        if ($colrow['Key'] == 'PRI') {
          $curr_tables[$table_name]['PK'] = $colrow['Field'];
        }
      }
    }
    
    echo '<pre>';
    
    foreach ($dest_tables as $table_name => $dest_table) {
      $curr_table = $curr_tables[$table_name];
      
      if ($curr_table == null) {
        // Create the table if it does not yet exist.
        echo "Create table {$table_name}.\n";
        
        $q = "CREATE TABLE {$table_name} (\n";
        foreach ($dest_table['Columns'] as $col_name => $col_def) {
          $q .= "  {$col_name} {$col_def},\n";
        }
        $q .= "  PRIMARY KEY ({$dest_table['PK']})\n";
        $q .= ")";
        echo "<b>Query:\n{$q}</b>\n";
        
        if ($_GET['action'] == 1) {
          $res = $this->query ($q);
          if ($res) echo 'Affected rows: ', $this->affected_rows($res), "\n";
        } else {
          $has_queries = true;
        }
        
        
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
          
          
          if ($curr_column == null) {
            echo "  Create column {$column_name}. New def: '{$dest_column}'\n";
            
            $q = "ALTER TABLE {$table_name} ADD COLUMN {$column_name} {$dest_column}";
            echo "    <b>Query: {$q}</b>\n";
            
            if ($_GET['action'] == 1) {
              $res = $this->query ($q);
              if ($res) echo '    Affected rows: ', $this->affected_rows($res), "\n";
            } else {
              $has_queries = true;
            }
            
            
          } else if ($curr_column != $dest_column) {
            echo "  Update col {$column_name}. Old def: '{$curr_column}' New def: '{$dest_column}'\n";
            
            $q = "ALTER TABLE {$table_name} MODIFY COLUMN {$column_name} {$dest_column}";
            echo "    <b>Query: {$q}</b>\n";
            
            if ($_GET['action'] == 1) {
              $res = $this->query ($q);
              if ($res) echo '    Affected rows: ', $this->affected_rows($res), "\n";
            } else {
              $has_queries = true;
            }
            
            
          } else {
            echo "  Column {$column_name} does not need to be changed\n";
          }
        }
      }
      
      echo "\n";
    }
    
    foreach ($curr_tables as $table_name => $curr_table) {
      $dest_table = $dest_tables[$table_name];
      
      if ($dest_table == null) {
        // Delete the table if it does not yet exist.
        echo "Delete table {$table_name}.\n";
        
        $q = "DROP TABLE {$table_name}";
        echo "<b>Query:\n{$q}</b>\n";
        
        if ($_GET['action'] == 1) {
          $res = $this->query ($q);
          if ($res) echo 'Affected rows: ', $this->affected_rows($res), "\n";
        } else {
          $has_queries = true;
        }
      }
    }
    
    echo '</pre>';
    
    if ($has_queries) {
      echo '<p>This update needs to make changes. ';
      echo '<a href="database_layout_sync.php?action=1">Run these queries</a></p>';
    }
  }
  
  
  /**
  * Does the actual outputting of the file objects (and their sub-objects) to the database
  *
  * @param array $files The file objects to save to the database
  **/
  public function output ($files) {
    global $dpqProjectID, $dpgProjectName, $dpgLicenseText;
    
    $res = $this->connect();
    if (! $res) {
      echo "<p>Unable to connect to database!";
      return false;
    }
    
    $this->query ("TRUNCATE projects");
    $this->query ("TRUNCATE files");
    $this->query ("TRUNCATE functions");
    $this->query ("TRUNCATE arguments");
    $this->query ("TRUNCATE classes");
    $this->query ("TRUNCATE packages");
    $this->query ("TRUNCATE interfaces");
    $this->query ("TRUNCATE variables");
    $this->query ("TRUNCATE constants");
    $this->query ("TRUNCATE authors");
    
    $insert_data = array();
    $insert_data['id'] = $dpqProjectID;
    $insert_data['name'] = $this->sql_safen ($dpgProjectName);
    $insert_data['license'] = $this->sql_safen ($dpgLicenseText);
    $q = $this->create_insert_query('projects', $insert_data);
    $this->query($q);
    
    // get all of the unique package names, and create packages
    $packages = array();
    foreach ($files as $file) {
      if ($file->package != null) {
        if (! isset($packages[$file->package])) {
          $insert_data = array();
          $insert_data['name'] = $this->sql_safen ($file->package);
          $q = $this->create_insert_query('packages', $insert_data);
          
          $this->query($q);
          $packages[$file->package] = $this->insert_id();
        }
        
      } else {
        $needs_default_package = true;
      }
    }
    
    if ($needs_default_package) {
      $insert_data = array();
      $insert_data['name'] = $this->sql_safen ('Default');
      $q = $this->create_insert_query('packages', $insert_data);
      
      $this->query($q);
      $default_id = $this->insert_id();
    }
    
    // go through all the files
    foreach ($files as $file) {
      // the file itself
      $package = $packages[$file->package];
      if ($package == null) $package = $default_id;
      $package = $this->sql_safen($package);
      
      $insert_data = array();
      $insert_data['name'] = $this->sql_safen($file->name);
      $insert_data['description'] = $this->sql_safen($file->description);
      $insert_data['source'] = $this->sql_safen($file->source);
      $insert_data['sinceversion'] = $this->sql_safen($file->since);
      $insert_data['packageid'] = $package;
      
      $q = $this->create_insert_query('files', $insert_data);
      $this->query ($q);
      $file_id = $this->insert_id ();
      
      // this files functions
      foreach ($file->functions as $function) {
        $this->save_function ($function, $file_id);
      }

      // this files classes
      foreach ($file->classes as $class) {
        if ($class instanceof ParserClass) {
          $this->save_class ($class, $file_id);
        } else if ($class instanceof ParserInterface) {
          $this->save_interface ($class, $file_id);
        }
      }
      
      // this files constants
      foreach ($file->constants as $constant) {
        $this->save_constant($constant, $file_id);
      }
      
      // The authors
      foreach ($file->authors as $author) {
        $this->save_author (LINK_TYPE_FILE, $file_id, $author);
      }
    }
    
    return true;
  }

  /**
  * Saves a function to the database
  **/
  private function save_function ($function, $file_id, $class_id = null, $interface_id = null) {
    // prepare data for inserting
    $insert_data = array();
    $insert_data['static'] = 0;
    $insert_data['final'] = 0;
    $insert_data['name'] = $this->sql_safen($function->name);
    $insert_data['description'] = $this->sql_safen($function->description);
    $insert_data['fileid'] = $file_id;
    $insert_data['sinceversion'] = $this->sql_safen($function->since);
    
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
      $insert_data['arguments'] = $this->sql_safen(implode (', ', $args));
      
    } else {
      $insert_data['arguments'] = "''";
    }
    
    // build query from prepared data
    $q = $this->create_insert_query('functions', $insert_data);
    $this->query ($q);
    $function_id = $this->insert_id ();
    
    
    // insert authors
    foreach ($function->authors as $author) {
      $this->save_author (LINK_TYPE_FUNCTION, $function_id, $author);
    }
    
    
    // insert Arguments
    foreach ($function->args as $arg) {
      $insert_data = array();
      $insert_data['name'] = $this->sql_safen($arg->name);
      $insert_data['type'] = $this->sql_safen($arg->type);
      $insert_data['defaultvalue'] = $this->sql_safen($arg->default);
      $insert_data['description'] = $this->sql_safen($arg->description);
      $insert_data['functionid'] = $this->sql_safen($function_id);
      
      // build query from prepared data
      $q = $this->create_insert_query('arguments', $insert_data);
      $this->query ($q);
    }
    
    
    // insert return value
    if ($function->return != null) {
      $insert_data = array();
      $insert_data['name'] = $this->sql_safen('__RETURN__');
      $insert_data['type'] = $this->sql_safen($function->return->type);
      $insert_data['description'] = $this->sql_safen($function->return->description);
      $insert_data['functionid'] = $this->sql_safen($function_id);
      
      // build query from prepared data
      $q = $this->create_insert_query('arguments', $insert_data);
      $this->query ($q);
    }
  }
  
  
  /**
  * Saves a class to the database
  **/
  private function save_class ($class, $file_id) {
    // prepare the data for inserting
    $insert_data = array();
    $insert_data['abstract'] = 0;
    $insert_data['final'] = 0;
    $insert_data['name'] = $this->sql_safen($class->name);
    $insert_data['description'] = $this->sql_safen($class->description);
    $insert_data['extends'] = $this->sql_safen($class->extends);
    $insert_data['visibility'] = $this->sql_safen($class->visibility);
    $insert_data['fileid'] = $file_id;
    $insert_data['sinceversion'] = $this->sql_safen($class->since);
    
    if ($class->abstract) $insert_data['abstract'] = 1;
    if ($class->final) $insert_data['final'] = 1;
    
    // Build and process query from prepared data
    $q = $this->create_insert_query('classes', $insert_data);
    $this->query ($q);
    $class_id = $this->insert_id ();
    
    
    // process functions
    foreach ($class->functions as $function) {
      $this->save_function($function, $file_id, $class_id);
    }
    
    // process variables
    foreach ($class->variables as $variable) {
      $this->save_variable($variable, $class_id);
    }
    
    // insert authors
    foreach ($class->authors as $author) {
      $this->save_author (LINK_TYPE_CLASS, $class_id, $author);
    }
  }
  
  
  /**
  * Saves an interface to the database
  **/
  private function save_interface ($interface, $file_id) {
    // prepare the data for inserting
    $insert_data = array();
    $insert_data['name'] = $this->sql_safen($interface->name);
    $insert_data['description'] = $this->sql_safen($interface->description);
    $insert_data['extends'] = $this->sql_safen($interface->extends);
    $insert_data['visibility'] = $this->sql_safen($interface->visibility);
    $insert_data['fileid'] = $file_id;
    $insert_data['sinceversion'] = $this->sql_safen($interface->since);
    
    
    // Build and process query from prepared data
    $q = $this->create_insert_query('interfaces', $insert_data);
    $this->query ($q);
    $interface_id = $this->insert_id ();


    // process functions
    foreach ($interface->functions as $function) {
      $this->save_function ($function, $file_id, null, $interface_id);
    }
    
    // insert authors
    foreach ($interface->authors as $author) {
      $this->save_author (LINK_TYPE_INTERFACE, $interface_id, $author);
    }
  }
  
  
  /**
  * Saves a variable to the database
  **/
  private function save_variable ($variable, $class_id = null, $interface_id = null) {
    // prepare data for inserting
    $insert_data = array();
    $insert_data['static'] = 0;
    $insert_data['name'] = $this->sql_safen($variable->name);
    $insert_data['description'] = $this->sql_safen($variable->description);
    //$insert_data['visibility'] = $this->sql_safen($variable->visibility);
    $insert_data['sinceversion'] = $this->sql_safen($variable->since);
    
    
    // Class-specific details
    if ($class_id != null) {
      $insert_data['classid'] = $class_id;
      
    // Interface-specific details
    } else if ($interface_id != null) {
      $insert_data['interfaceid'] = $interface_id;
    }
    
    if ($variable->static) $insert_data['static'] = 1;
    
    
    // Build and process query from prepared data
    $q = $this->create_insert_query('variables', $insert_data);
    $this->query ($q);
    $variable_id = $this->insert_id ();
    
    // insert authors
    foreach ($variable->authors as $author) {
      $this->save_author (LINK_TYPE_VARIABLE, $variable_id, $author);
    }
  }
  
  
  /**
  * Saves a constant to the database
  **/
  private function save_constant ($constant, $file_id = null) {
    // prepare data for inserting
    $insert_data = array();
    $insert_data['name'] = $this->sql_safen($constant->name);
    $insert_data['value'] = $this->sql_safen($constant->value);
    $insert_data['description'] = $this->sql_safen($constant->description);
    $insert_data['fileid'] = $this->sql_safen($file_id);
    $insert_data['sinceversion'] = $this->sql_safen($constant->since);
    
    
    // Build and process query from prepared data
    $q = $this->create_insert_query('constants', $insert_data);
    $this->query ($q);
    $constant_id = $this->insert_id ();
    
    // insert authors
    foreach ($constant->authors as $author) {
      $this->save_author (LINK_TYPE_CONSTANT, $constant_id, $author);
    }
  }
  
  
  /**
  * Saves author information about an item
  **/
  private function save_author ($link_type, $link_id, $author) {
    $insert_data = array();
    $insert_data['linkid'] = $this->sql_safen($link_id);
    $insert_data['linktype'] = $this->sql_safen($link_type);
    $insert_data['name'] = $this->sql_safen($author->name);
    $insert_data['email'] = $this->sql_safen($author->email);
    $insert_data['description'] = $this->sql_safen($author->description);
    
    // Build and process query from prepared data
    $q = $this->create_insert_query('authors', $insert_data);
    $this->query ($q);
  }
  
}

?>
