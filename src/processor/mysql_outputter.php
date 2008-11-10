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
class MysqlOutputter extends DatabaseOutputter {
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
  * Connects to the MySQL database
  **/
  protected function connect () {
    $this->db = @mysql_connect($this->server, $this->username, $this->password);
    if ($this->db == false) return false;
    mysql_select_db ($this->database, $this->db);
  }
  
  /**
  * Executes a MySQL query
  */
  protected function query ($query) {
    $return = mysql_query ($query, $this->db);
    if ($return === false) {
      echo "<p>Error in query <em>{$query}</em>. MySQL reported the following: <em>" . mysql_error() . "</em></p>";
    }
    return $return;
  }
  
  /**
  * Safens some input
  * @param string $input The input to safen
  **/
  protected function sql_safen ($input) {
    if ($input === null) {
      return 'NULL';
    } else if (is_integer ($input)) {
      return $input;
    } else {
      return "'" . mysql_real_escape_string($input, $this->db) . "'";
    }
  }
  
  /**
  * Fetches a row from the database (numerical)
  **/
  protected function fetch_row ($res) {
    return mysql_fetch_row ($res);
  }
  
  /**
  * Fetches a row from the database (assoc)
  **/
  protected function fetch_assoc ($res) {
    return mysql_fetch_assoc ($res);
  }
  
  /**
  * Returns the number of rows affected in the last query
  **/
  protected function affected_rows () {
    return mysql_affected_rows();
  }
  
  /**
  * Returns the autogenerated id created in the last query
  **/
  protected function insert_id () {
    return mysql_insert_id ();
  }
}

?>
