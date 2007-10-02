<?php
require_once 'head.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
	$name = trim($_GET['name']);
	if ($name == '') {
		fatal ("<p>Invalid function name!</p>");
	}
	$name = mysql_escape ($name);
	$where = "Functions.Name LIKE '{$name}'";
} else {
	$where = "Functions.ID = {$id}";
}


// Get the details of this file
$q = "SELECT Functions.ID, Functions.Name, Functions.Description, Files.Name AS Filename
	FROM Functions
	INNER JOIN Files ON Functions.FileID = Files.ID
	WHERE {$where} LIMIT 1";
$res = execute_query ($q);
$row = mysql_fetch_assoc ($res);
echo "<h2>{$row['Name']}</h2>";
$filename_clean = htmlentities(urlencode($row['Filename']));
echo "<p>File: <a href=\"file.php?name={$filename_clean}\">" . htmlentities($row['Filename']) . "</a></p>\n";
echo "<p>{$row['Description']}</p>";
$id = $row['ID'];


// Show functions
$q = "SELECT ID, Name, Type, Description FROM Parameters WHERE FunctionID = {$id}";
$res = execute_query ($q);
echo "<table class=\"parameter-list\">\n";
echo "<tr><th>Name</th><th>Description</th></tr>\n";
while ($row = mysql_fetch_assoc ($res)) {
	echo "<tr>";
	echo "<td><code>{$row['Type']} {$row['Name']}</code></td>";
	echo "<td>{$row['Description']}</td>";
	echo "</tr>\n";
}
echo "</table>\n";

require_once 'foot.php';
?>

