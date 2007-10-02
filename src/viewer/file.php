<?php
require_once 'head.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
	$name = trim($_GET['name']);
	if ($name == '') {
		fatal ("<p>Invalid filename!</p>");
	}
	$name = mysql_escape ($name);
	$where = "Name LIKE '{$name}'";
} else {
	$where = "ID = {$id}";
}


// Get the details of this file
$q = "SELECT ID, Name, Description FROM Files WHERE {$where} LIMIT 1";
$res = execute_query ($q);
$row = mysql_fetch_assoc ($res);
echo "<h2>{$row['Name']}</h2>";
echo "<p>{$row['Description']}</p>";
$id = $row['ID'];


// Show functions
$q = "SELECT ID, Name, Description, Parameters FROM Functions WHERE FileID = {$id}";
$res = execute_query ($q);
echo "<table class=\"function-list\">\n";
echo "<tr><th>Name</th><th>Description</th></tr>\n";
while ($row = mysql_fetch_assoc ($res)) {
	echo "<tr>";
	echo "<td><code><a href=\"function.php?id={$row['ID']}\">";
	echo "{$row['Name']}({$row['Parameters']})</a></code></td>";
	echo "<td>{$row['Description']}</td>";
	echo "</tr>\n";
}
echo "</table>\n";

require_once 'foot.php';
?>

