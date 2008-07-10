<?php
require_once 'config.php';

session_start();

$dbc = mysql_connect (CONFIG::Server, CONFIG::Username, CONFIG::Password);
mysql_select_db (CONFIG::Database);


/**
* Executes a MySQL query
*
* @param $q The query to execute
* @return The result from the query
**/
function execute_query($q) {
	global $dbc;
	$res = mysql_query ($q, $dbc);
	if ($res === false) {
		echo mysql_error ($dbc);
	}
	return $res;
}


/**
* Escapes a string ready for putting into MySQL
*
* @param string $string The string to escape
*/
function mysql_escape($string) {
	global $dbc;
	return mysql_real_escape_string ($string, $dbc);
}


/**
* Determines the link for a specified name (might be a class or an interface)
*
* @param string $name The name to check
* @return string A piece of HTML usable to represent the object, as a link if possible
**/
function get_object_link($name) {

  // check classes
  $sql_name = mysql_escape($name);
  $q = "SELECT ID FROM Classes WHERE Name = '{$sql_name}' LIMIT 1";
  $res = execute_query($q);
  
  if (mysql_num_rows($res) != 0) {
    $row = mysql_fetch_assoc($res);
    $ret = "<a href=\"class.php?id={$row['ID']}\">{$name}</a>";
    return $ret;
  }
  
  // check interfaces
  $sql_name = mysql_escape($name);
  $q = "SELECT ID FROM Interfaces WHERE Name = '{$sql_name}' LIMIT 1";
  $res = execute_query($q);
  
  if (mysql_num_rows($res) != 0) {
    $row = mysql_fetch_assoc($res);
    $ret = "<a href=\"interface.php?id={$row['ID']}\">{$name}</a>";
    return $ret;
  }
  
  return $name;
}
?>
