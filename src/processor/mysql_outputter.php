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
* @since 0.1
**/

/**
* Outputs the tree as MySQL
**/
class MysqlOutputter {
  private $username;
  private $password;
  private $server;
  private $database;
  
  private $db;
    
  /**
  * Connects to the db
  */
  public function __construct ($username, $password, $server, $database) {
    $this->username = $username;
    $this->password = $password;
    $this->server = $server;
    $this->database = $database;
  }
  
  /**
  * Closes connection to the db
  */
  public function __destruct () {
    if ($this->db) mysql_close ($this->db);
  }

  /**
  * Executes a MySQL query
  */
  private function query ($query) {
    $return = mysql_query ($query, $this->db);
    if ($return === false) {
      echo "<p>Error in query <em>{$query}</em>. MySQL reported the following: <em>" . mysql_error() . "</em></p>";
    }
    return $return;
  }

  /**
  * @param string $input Safens some input
  **/
  private function sql_safen ($input) {
    if ($input === null) {
      return 'NULL';
    } else if (is_integer ($input)) {
      return $input;
    } else {
      return "'" . mysql_real_escape_string($input, $this->db) . "'";
    }
  }
  
  private function connect () {
    $this->db = @mysql_connect($this->server, $this->username, $this->password);
    if ($this->db == false) return false;
    mysql_select_db ($this->database, $this->db);
  }
  
  
  /**
  * Updates the MySQL layout to match the layout file
  * NOTE: currently only supports column and table adding and updating, not removal.
  * 
  * @param string $layout_filename The name of hte layout file to match
  **/
  public function check_layout ($layout_filename) {
    $layout_lines = file ($layout_filename);
    
    $this->connect();
    
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
          
        case 'ENGINE':
          //$dest_tables[$table]['Engine'] = $words[1];
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
    
    $tblres = $this->query('SHOW TABLES');
    while ($tblrow = mysql_fetch_row($tblres)) {
      $curr_tables[$tblrow[0]] = array();
      
      $colres = $this->query('SHOW COLUMNS IN ' . $tblrow[0]);
      
      while ($colrow = mysql_fetch_assoc($colres)) {
        $def = $colrow['Type'];
        if ($colrow['Null'] == 'NO') $def .= ' NOT NULL';
        if ($colrow['Extra']) $def .= ' ' . $colrow['Extra'];
        
        $curr_tables[$tblrow[0]]['Columns'][$colrow['Field']] = $def;
        
        if ($colrow['Key'] == 'PRI') {
          $curr_tables[$tblrow[0]]['PK'] = $colrow['Field'];
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
          if ($res) echo 'Affected rows: ', mysql_affected_rows(), "\n";
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
              if ($res) echo '    Affected rows: ', mysql_affected_rows(), "\n";
            } else {
              $has_queries = true;
            }
            
            
          } else if ($curr_column != $dest_column) {
            echo "  Update col {$column_name}. Old def: '{$curr_column}' New def: '{$dest_column}'\n";
            
            $q = "ALTER TABLE {$table_name} MODIFY COLUMN {$column_name} {$dest_column}";
            echo "    <b>Query: {$q}</b>\n";
            
            if ($_GET['action'] == 1) {
              $res = $this->query ($q);
              if ($res) echo '    Affected rows: ', mysql_affected_rows(), "\n";
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
    
    echo '</pre>';
    
    if ($has_queries) {
      echo '<p>This update needs to make changes. ';
      echo '<a href="database_layout_sync.php?action=1">Run these queries</a></p>';
    }
  }
  
  
  /**
  * Does the actual outputting of the file objects (and their sub-objects) to the MySQL database
  *
  * @param array $files The file objects to save to the MySQL database
  **/
  public function output ($files) {
    global $dpqProjectID, $dpgProjectName, $dpgLicenseText;
    
    $this->connect();
    $this->query ("TRUNCATE Projects");
    $this->query ("TRUNCATE Files");
    $this->query ("TRUNCATE Functions");
    $this->query ("TRUNCATE Arguments");
    $this->query ("TRUNCATE Classes");
    $this->query ("TRUNCATE Packages");
    $this->query ("TRUNCATE Interfaces");
    $this->query ("TRUNCATE Variables");
    $this->query ("TRUNCATE Constants");
    $this->query ("TRUNCATE Authors");
    
    $proj_name = $this->sql_safen ($dpgProjectName);
    $lic_text = $this->sql_safen ($dpgLicenseText);
    $q = "INSERT INTO Projects (ID, Name, License) VALUES ({$dpqProjectID}, {$proj_name}, {$lic_text})";
    $this->query($q);
    
    // get all of the unique package names, and create packages
    $packages = array();
    foreach ($files as $file) {
      if ($file->package != null) {
        if (! isset($packages[$file->package])) {
          $package_save = $this->sql_safen($file->package);
          $q = "INSERT INTO Packages (Name) VALUES ({$package_save})";
          $this->query($q);
          $packages[$file->package] = mysql_insert_id();
        }
        
      } else {
        $needs_default_package = true;
      }
    }
    
    if ($needs_default_package) {
      $q = "INSERT INTO Packages (Name) VALUES ('default')";
      $this->query($q);
      $default_id = mysql_insert_id();
    }
    
    // go through all the files
    foreach ($files as $file) {
      // the file itself
      $name = $this->sql_safen($file->name);
      $description = $this->sql_safen($file->description);
      $source = $this->sql_safen($file->source);
      $since = $this->sql_safen($file->since);
      
      $package = $packages[$file->package];
      if ($package == null) $package = $default_id;
      $package = $this->sql_safen($package);
      
      $q = "INSERT INTO Files SET
        Name = {$name},
        Description = {$description},
        Source = {$source},
        PackageID = {$package},
        SinceVersion = {$since}";
      $this->query ($q);
      $file_id = mysql_insert_id ();
      
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
  * Saves a function to the MySQL database
  **/
  private function save_function ($function, $file_id, $class_id = null, $interface_id = null) {
    // prepare data for inserting
    $insert_data = array();
    $insert_data['Name'] = $this->sql_safen($function->name);
    $insert_data['Description'] = $this->sql_safen($function->description);
    $insert_data['FileID'] = $file_id;
    $insert_data['SinceVersion'] = $this->sql_safen($function->since);
    
    // Class-specific details
    if ($class_id != null) {
      $insert_data['ClassID'] = $class_id;
      $insert_data['Visibility'] = $this->sql_safen($function->visibility);
      
    // Interface-specific details
    } else if ($interface_id != null) {
      $insert_data['InterfaceID'] = $interface_id;
      $insert_data['Visibility'] = $this->sql_safen($function->visibility);
    }
    
    // Return value
    if ($function->return_type != null) {
      $insert_data['ReturnType'] = $this->sql_safen($function->return_type);
      $insert_data['ReturnDescription'] = $this->sql_safen($function->return_description);
    }
    
    if ($function->static) $insert_data['Static'] = 1;
    if ($function->final) $insert_data['Final'] = 1;
    
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
      $insert_data['Arguments'] = $this->sql_safen(implode (', ', $args));
    }

    // build query from prepared data
    $q = "INSERT INTO Functions SET ";
    foreach ($insert_data as $key => $value) {
      if ($j++ > 0) $q .= ', ';
      $q .= "{$key} = {$value}";
    }
    $this->query ($q);
    $function_id = mysql_insert_id ();
    
    
    // insert authors
    foreach ($function->authors as $author) {
      $this->save_author (LINK_TYPE_FUNCTION, $function_id, $author);
    }
    
    
    // insert Arguments
    foreach ($function->args as $arg) {
      $insert_data = array();
      $insert_data['Name'] = $this->sql_safen($arg->name);
      $insert_data['Type'] = $this->sql_safen($arg->type);
      $insert_data['DefaultValue'] = $this->sql_safen($arg->default);
      $insert_data['Description'] = $this->sql_safen($arg->description);
      
      // build query from prepared data
      $q = "INSERT INTO Arguments SET FunctionID = {$function_id}";
      foreach ($insert_data as $key => $value) {
        $q .= ", {$key} = {$value}";
      }
      $this->query ($q);
    }
    
    
    // insert return value
    if ($function->return != null) {
      $insert_data = array();
      $insert_data['Name'] = $this->sql_safen('__RETURN__');
      $insert_data['Type'] = $this->sql_safen($function->return->type);
      $insert_data['Description'] = $this->sql_safen($function->return->description);
      
      // build query from prepared data
      $q = "INSERT INTO Arguments SET FunctionID = {$function_id}";
      foreach ($insert_data as $key => $value) {
        $q .= ", {$key} = {$value}";
      }
      $this->query ($q);
    }
  }


  /**
  * Saves a class to the MySQL database
  **/
  private function save_class ($class, $file_id) {
    // prepare the data for inserting
    $insert_data = array();
    $insert_data['Name'] = $this->sql_safen($class->name);
    $insert_data['Description'] = $this->sql_safen($class->description);
    $insert_data['Extends'] = $this->sql_safen($class->extends);
    $insert_data['Visibility'] = $this->sql_safen($class->visibility);
    $insert_data['FileID'] = $file_id;
    $insert_data['SinceVersion'] = $this->sql_safen($class->since);
    
    if ($class->abstract) $insert_data['Abstract'] = 1;
    if ($class->final) $insert_data['Final'] = 1;
    
    // Build and process query from prepared data
    $q = "INSERT INTO Classes SET ";
    foreach ($insert_data as $key => $value) {
      if ($j++ > 0) $q .= ', ';
      $q .= "{$key} = {$value}";
    }
    $this->query ($q);
    $class_id = mysql_insert_id ();


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
  * Saves an interface to the mysql database
  **/
  private function save_interface ($interface, $file_id) {
    // prepare the data for inserting
    $insert_data = array();
    $insert_data['Name'] = $this->sql_safen($interface->name);
    $insert_data['Description'] = $this->sql_safen($interface->description);
    $insert_data['Extends'] = $this->sql_safen($interface->extends);
    $insert_data['Visibility'] = $this->sql_safen($interface->visibility);
    $insert_data['FileID'] = $file_id;
    $insert_data['SinceVersion'] = $this->sql_safen($interface->since);
    
    
    // Build and process query from prepared data
    $q = "INSERT INTO Interfaces SET ";
    foreach ($insert_data as $key => $value) {
      if ($j++ > 0) $q .= ', ';
      $q .= "{$key} = {$value}";
    }
    $this->query ($q);
    $interface_id = mysql_insert_id ();


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
  * Saves a variable to the mysql database
  **/
  private function save_variable ($variable, $class_id = null, $interface_id = null) {
    // prepare data for inserting
    $insert_data = array();
    $insert_data['Name'] = $this->sql_safen($variable->name);
    $insert_data['Description'] = $this->sql_safen($variable->description);
    //$insert_data['Visibility'] = $this->sql_safen($variable->visibility);
    $insert_data['SinceVersion'] = $this->sql_safen($variable->since);
    
    
    // Class-specific details
    if ($class_id != null) {
      $insert_data['ClassID'] = $class_id;
      
    // Interface-specific details
    } else if ($interface_id != null) {
      $insert_data['InterfaceID'] = $interface_id;
    }
    
    if ($variable->static) $insert_data['Static'] = 1;
    
    // Build and process query from prepared data
    $q = "INSERT INTO Variables SET ";
    foreach ($insert_data as $key => $value) {
      if ($j++ > 0) $q .= ', ';
      $q .= "{$key} = {$value}";
    }
    $this->query ($q);
    $variable_id = mysql_insert_id ();
    
    // insert authors
    foreach ($variable->authors as $author) {
      $this->save_author (LINK_TYPE_VARIABLE, $variable_id, $author);
    }
  }
  
  
  /**
  * Saves a constant to the mysql database
  **/
  private function save_constant ($constant, $file_id = null) {
    // prepare data for inserting
    $insert_data = array();
    $insert_data['Name'] = $this->sql_safen($constant->name);
    $insert_data['Value'] = $this->sql_safen($constant->value);
    $insert_data['Description'] = $this->sql_safen($constant->description);
    $insert_data['FileID'] = $this->sql_safen($file_id);
    $insert_data['SinceVersion'] = $this->sql_safen($constant->since);
    
    
    // Build and process query from prepared data
    $q = "INSERT INTO Constants SET ";
    foreach ($insert_data as $key => $value) {
      if ($j++ > 0) $q .= ', ';
      $q .= "{$key} = {$value}";
    }
    $this->query ($q);
    $constant_id = mysql_insert_id ();
    
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
    $insert_data['LinkID'] = $this->sql_safen($link_id);
    $insert_data['LinkType'] = $this->sql_safen($link_type);
    $insert_data['Name'] = $this->sql_safen($author->name);
    $insert_data['Email'] = $this->sql_safen($author->email);
    $insert_data['Description'] = $this->sql_safen($author->description);
    
    // Build and process query from prepared data
    $q = "INSERT INTO Authors SET ";
    foreach ($insert_data as $key => $value) {
      if ($j++ > 0) $q .= ', ';
      $q .= "{$key} = {$value}";
    }
    $this->query ($q);
  }
  
}

?>
