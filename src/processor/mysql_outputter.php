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


	// MySQL stuff
	public function __construct ($username, $password, $server, $database) {
		$this->db = mysql_connect($server, $username, $password);
		mysql_select_db ($database, $this->db);
	}

	public function __destruct () {
		mysql_close ($this->db);
	}

	private function query ($query) {
		$return = mysql_query ($query, $this->db);
		if ($return === false) {
			echo "<p>Error in query <em>{$query}</em>. MySQL reported the following: <em>" . mysql_error() . "</em></p>";
		}
		return $return;
	}

	private function sql_safen ($input) {
		if ($input == null) {
			return 'NULL';
		} else if (is_integer ($input)) {
			return $input;
		} else {
			return "'" . mysql_real_escape_string($input, $this->db) . "'";
		}
	}



	// Saves to the db
	public function output ($files) {
		$this->query ("TRUNCATE TABLE Files");
		$this->query ("TRUNCATE TABLE Functions");
		$this->query ("TRUNCATE TABLE Parameters");
		$this->query ("TRUNCATE TABLE Classes");		
		

		foreach ($files as $file) {
			// the file itself
			$name = $this->sql_safen($file->name);
			$description = $this->sql_safen($file->description);
			
			// set the file packages to a space-seperated string of names. will probably become another table for better searching.
			if ($file->packages != null) {
			  $package = $this->sql_safen(implode(' ', $file->packages));
			} else {
			  $package = 'NULL';
			}
			
			$q = "INSERT INTO Files SET Name = {$name}, Description = {$description}, Packages = {$package}";
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
				}
			}
	
		}	
	}



	private function save_function ($function, $file_id, $class_id = null) {
		// prepare data for inserting
		$insert_data = array();
		$insert_data['Name'] = $this->sql_safen($function->name);
		$insert_data['Description'] = $this->sql_safen($function->description);
		$insert_data['FileID'] = $file_id;

		// Class id
		if ($class_id != null) $insert_data['ClassID'] = $class_id;

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


	private function save_class ($class, $file_id) {
		// prepare the data for inserting
		$insert_data = array();
		$insert_data['Name'] = $this->sql_safen($class->name);
		$insert_data['Description'] = $this->sql_safen($class->description);
		$insert_data['Extends'] = $this->sql_safen($class->extends);
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

}

?>
