<?php
require_once 'head.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
	$name = trim($_GET['name']);
	if ($name == '') {
		fatal ("<p>Invalid interface name!</p>");
	}
	$name = mysql_escape ($name);
	$where = "Interfaces.Name LIKE '{$name}'";
} else {
	$where = "Interfaces.ID = {$id}";
}


// Get the details of this class
$q = "SELECT Interfaces.ID, Interfaces.Name, Interfaces.Description, Files.Name AS Filename
	FROM Interfaces
	INNER JOIN Files ON Interfaces.FileID = Files.ID
	WHERE {$where} LIMIT 1";
$res = execute_query ($q);
$row = mysql_fetch_assoc ($res);
echo "<h2>{$row['Name']}</h2>";
$filename_clean = htmlentities(urlencode($row['Filename']));
echo "<p>File: <a href=\"file.php?name={$filename_clean}\">" . htmlentities($row['Filename']) . "</a></p>\n";
echo "<p>{$row['Description']}</p>";
$id = $row['ID'];


// Show functions
$q = "SELECT ID, Name, Description, Parameters FROM Functions WHERE InterfaceID = {$id}";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h3>Functions</h3>";
  echo "<table class=\"function-list\">\n";
  echo "<tr><th>Name</th><th>Description</th></tr>\n";
  while ($row = mysql_fetch_assoc ($res)) {
    // encode for output
    $row['Name'] = htmlspecialchars($row['Name']);
    if ($row['Description'] == null) {
      $row['Description'] = '&nbsp;';
    } else {
      $row['Description'] = htmlspecialchars($row['Description']);
    }
    $row['Parameters'] = htmlspecialchars($row['Parameters']);
      
    // display
	  echo "<tr>";
	  echo "<td><code><a href=\"function.php?id={$row['ID']}\">";
	  echo "{$row['Name']}({$row['Parameters']})</a></code></td>";
	  echo "<td>{$row['Description']}</td>";
	  echo "</tr>\n";
  }
  echo "</table>\n";
}

require_once 'foot.php';
?>
