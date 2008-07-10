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


// Get the details of this function
$q = "SELECT Functions.ID, Functions.Name, Functions.Description, Files.Name AS Filename, Functions.ClassID, Classes.Name AS Class
	FROM Functions
	INNER JOIN Files ON Functions.FileID = Files.ID
	LEFT JOIN Classes ON Functions.ClassID = Classes.ID
	WHERE {$where} LIMIT 1";
$res = execute_query ($q);
$row = mysql_fetch_assoc ($res);
echo "<h2>{$row['Name']}</h2>";
$filename_clean = htmlentities(urlencode($row['Filename']));
echo "<p>File: <a href=\"file.php?name={$filename_clean}\">" . htmlentities($row['Filename']) . "</a></p>\n";
if ($row['ClassID'] != null) {
	echo "<p>Class: <a href=\"class.php?id={$row['ClassID']}\">{$row['Class']}</a></p>\n";
}
echo $row['Description'];
$id = $row['ID'];


// Show parameters
$q = "SELECT ID, Name, Type, Description FROM Parameters WHERE FunctionID = {$id}";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h3>Parameters</h3>";
  echo "<table class=\"parameter-list\">\n";
  echo "<tr><th>Name</th><th>Description</th></tr>\n";
  while ($row = mysql_fetch_assoc ($res)) {
    $row['Name'] = htmlspecialchars($row['Name']);
    if ($row['Description'] == null) {
      $row['Description'] = '&nbsp;';
    } else {
      $row['Description'] = htmlspecialchars($row['Description']);
      $row['Description'] = str_replace("\n", '<br>', $row['Description']);      
    }
    $row['Type'] = htmlspecialchars($row['Type']);
    
	  echo "<tr>";
	  echo "<td><code>{$row['Type']} {$row['Name']}</code></td>";
	  echo "<td>{$row['Description']}</td>";
	  echo "</tr>\n";
  }
  echo "</table>\n";
}

require_once 'foot.php';
?>

