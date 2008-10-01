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
* @package processor
* @package output
**/

/**
* Outputs the tree as MySQL
**/
class MysqlOutputter {
  private $db;

  /**
  * Connects to the db
  */
  public function __construct ($username, $password, $server, $database) {
    $this->db = mysql_connect($server, $username, $password);
    mysql_select_db ($database, $this->db);
  }
  
  /**
  * Closes connection to the db
  */
  public function __destruct () {
    mysql_close ($this->db);
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
    if ($input == null) {
      return 'NULL';
    } else if (is_integer ($input)) {
      return $input;
    } else {
      return "'" . mysql_real_escape_string($input, $this->db) . "'";
    }
  }

  /**
  * Does the actual outputting of the file objects (and their sub-objects) to the MySQL database
  *
  * @param array $files The file objects to save to the MySQL database
  **/
  public function output ($files) {
    global $dpgProjectName;
    
    $this->query ("TRUNCATE TABLE Files");
    $this->query ("TRUNCATE TABLE Functions");
    $this->query ("TRUNCATE TABLE Parameters");
    $this->query ("TRUNCATE TABLE Classes");		
    $this->query ("TRUNCATE TABLE Packages");
    $this->query ("TRUNCATE TABLE Interfaces");
    $this->query ("TRUNCATE TABLE Variables");
    $this->query ("TRUNCATE TABLE Projects");
    
    $proj_name = $this->sql_safen ($dpgProjectName);
    $q = "INSERT INTO Projects (Name) VALUES ({$proj_name})";
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
      
      $package = $packages[$file->package];
      if ($package == null) $package = $default_id;
      $package = $this->sql_safen($package);
      
      $q = "INSERT INTO Files SET Name = {$name}, Description = {$description}, Source = {$source}, PackageID = {$package}";
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
  
    }	
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
    
    // build params string
    if (count($function->params) > 0) {
      $params = array();
      foreach ($function->params as $param) {
        if ($param->type != null) {
          $params[] = $param->type . ' ' . $param->name;
        } else {
          $params[] = $param->name;
        }
      }
      $insert_data['Parameters'] = $this->sql_safen(implode (', ', $params));
    }

    // build query from prepared data
    $q = "INSERT INTO Functions SET ";
    foreach ($insert_data as $key => $value) {
      if ($j++ > 0) $q .= ', ';
      $q .= "{$key} = {$value}";
    }
    $this->query ($q);
    $function_id = mysql_insert_id ();


    // insert parameters
    foreach ($function->params as $param) {
      $insert_data = array();
      $insert_data['Name'] = $this->sql_safen($param->name);
      $insert_data['Type'] = $this->sql_safen($param->type);
      $insert_data['Description'] = $this->sql_safen($param->description);

      // build query from prepared data
      $q = "INSERT INTO Parameters SET FunctionID = {$function_id}";
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
      $q = "INSERT INTO Parameters SET FunctionID = {$function_id}";
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

    if ($class->abstract) $insert_data['Abstract'] = 1;


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

    // Class-specific details
    if ($class_id != null) {
      $insert_data['ClassID'] = $class_id;
      
    // Interface-specific details
    } else if ($interface_id != null) {
      $insert_data['InterfaceID'] = $interface_id;
    }
    

    // Build and process query from prepared data
    $q = "INSERT INTO Variables SET ";
    foreach ($insert_data as $key => $value) {
      if ($j++ > 0) $q .= ', ';
      $q .= "{$key} = {$value}";
    }
    $this->query ($q);
  }

}

?>
