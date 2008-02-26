<?php
require_once 'head.php';


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
	$name = trim($_GET['name']);
	if ($name == '') {
		fatal ("<p>Invalid class name!</p>");
	}
	$name = mysql_escape ($name);
	$where = "Classes.Name LIKE '{$name}'";
} else {
	$where = "Classes.ID = {$id}";
}


// Get the details of this class
$q = "SELECT Classes.ID, Classes.Name, Classes.Description, Files.Name AS Filename
	FROM Classes
	INNER JOIN Files ON Classes.FileID = Files.ID
	WHERE {$where} LIMIT 1";
$res = execute_query ($q);
$row = mysql_fetch_assoc ($res);
echo "<h2>{$row['Name']}</h2>";
$filename_clean = htmlentities(urlencode($row['Filename']));
echo "<p>File: <a href=\"file.php?name={$filename_clean}\">" . htmlentities($row['Filename']) . "</a></p>\n";
echo "<p>{$row['Description']}</p>";
$id = $row['ID'];


// Show functions
$q = "SELECT ID, Name, Description, Parameters FROM Functions WHERE ClassID = {$id}";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h2>Functions</h2>";
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

