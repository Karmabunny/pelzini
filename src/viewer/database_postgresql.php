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
* The PostgreSQL wrapper functions
* @package Viewer
* @author Josh Heidenreich
* @since 0.2
**/


/**
* Connects to the PostgreSQL database
**/
function db_connect() {
  global $db_connection, $dvgDatabaseSettings;
  
  $connect = '';
  if (isset($dvgDatabaseSettings['server'])) $connect .= "host='{$dvgDatabaseSettings['server']}' ";
  if (isset($dvgDatabaseSettings['username'])) $connect .= "user='{$dvgDatabaseSettings['username']}' ";
  if (isset($dvgDatabaseSettings['password'])) $connect .= "password='{$dvgDatabaseSettings['password']}' ";
  if (isset($dvgDatabaseSettings['name'])) $connect .= "dbname='{$dvgDatabaseSettings['name']}' ";
  
  $db_connection = pg_connect($connect);
}

/**
* Makes a query to the PostgreSQL database
**/
function db_query($q) {
  //echo "<pre>{$q}</pre>";
  
  return pg_query($q);
}

/**
* Escapses a string for use by the PostgreSQL database
**/
function db_escape($str) {
  return pg_escape_string($str);
}

/**
* Quotes a string as nessasary for use by hte PostgreSQL database
* The result will be different depending on the type of the input.
*  - a number will be left as is
*  - a string will be quoted
*  - a null value will be returned as NULL
**/
function db_quote($str) {
  if ($str === null) {
    return 'NULL';
    
  } else if (is_int($str)) {
    return $str;
    
  } else {
    return "'" . pg_escape_string($str) . "'";
  }
}

/**
* Fetches a PostgreSQL result set as an associative array
**/
function db_fetch_assoc($res) {
  return pg_fetch_assoc($res);
}

/**
* Returns the number of rows in a PostgreSQL result set
**/
function db_num_rows($res) {
  return pg_num_rows($res);
}

/**
* Returns the number of rows affected by the last PostgreSQL query that was executed
**/
function db_affected_rows($res) {
  return pg_affected_rows($res);
}

/**
* Returns the last unique ID generated by a query
**/
function db_insert_id() {
  $res = $this->query ('SELECT LASTVAL()');
  $row = $this->fetch_row ($res);
  return $row[0];
}
?>
