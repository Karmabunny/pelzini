<?php
require_once 'config.php';

$dbc = mysql_connect (CONFIG::Server, CONFIG::Username, CONFIG::Password);
mysql_select_db (CONFIG::Database);


/**
* Executes a MySQL query
*
* @param $q The query to execute
* @return The result from the query
**/
function execute_query ($q) {
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
function mysql_escape ($string) {
	global $dbc;
	return mysql_real_escape_string ($string, $dbc);
}
?>
