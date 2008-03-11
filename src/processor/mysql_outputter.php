<?php
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
		$this->query ("TRUNCATE TABLE Files");
		$this->query ("TRUNCATE TABLE Functions");
		$this->query ("TRUNCATE TABLE Parameters");
		$this->query ("TRUNCATE TABLE Classes");		
		$this->query ("TRUNCATE TABLE Packages");
  	$this->query ("TRUNCATE TABLE FilePackages");
  	$this->query ("TRUNCATE TABLE Interfaces");
  	
		// get all of the unique package names, and create packages
		$packages = array();
		foreach ($files as $file) {
		  if ($file->packages != null) {
		    foreach ($file->packages as $package) {
    		  if (! isset($packages[$package])) {
    		    $package_save = $this->sql_safen($package);
    		    $q = "INSERT INTO Packages (Name) VALUES ({$package_save})";
    		    $this->query($q);
    		    $packages[$package] = mysql_insert_id();
    		  }
    		}
      }
	  }
	  
	  // go through all the files
		foreach ($files as $file) {
			// the file itself
			$name = $this->sql_safen($file->name);
			$description = $this->sql_safen($file->description);
			$q = "INSERT INTO Files SET Name = {$name}, Description = {$description}";
			$this->query ($q);
			$file_id = mysql_insert_id ();

			// the file packages
			if ($file->packages != null) {
			  foreach ($file->packages as $package) {
			    $package_id = $packages[$package];
  		    $q = "INSERT INTO FilePackages (FileID, PackageID) VALUES ({$file_id}, {$package_id})";
  		    $this->query($q);
			  }
			}
			
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
			$this->save_function ($function, $file_id, $class_id);
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

}

?>
