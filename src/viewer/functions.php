<?php
require_once 'config.php';

$dbc = mysql_connect (DBINFO::Server, DBINFO::Username, DBINFO::Password);
mysql_select_db (DBINFO::Database);


function execute_query ($q) {
	global $dbc;
	$res = mysql_query ($q, $dbc);
	if ($res === false) {
		echo mysql_error ($dbc);
	}
	return $res;
}

function mysql_escape ($string) {
	global $dbc;
	return mysql_real_escape_string ($string, $dbc);
}
?>
