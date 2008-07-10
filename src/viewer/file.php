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
echo "<p><small><a href=\"file_source.php?id={$row['ID']}\">View Source</a></small></p>";
$id = $row['ID'];


// Show packages
$q = "SELECT Packages.ID, Packages.Name FROM Packages
 INNER JOIN FilePackages ON FilePackages.PackageID = Packages.ID
 WHERE FilePackages.FileID = {$id}";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h3>Packages</h3>";
  echo "<ul>\n";
  while ($row = mysql_fetch_assoc ($res)) {
    $row['Name'] = htmlspecialchars($row['Name']);
	  echo "<li><a href=\"package.php?id={$row['ID']}\">{$row['Name']}</a></li>";  }
  echo "</ul>\n";
}


// Show classes
$q = "SELECT ID, Name, Description FROM Classes WHERE FileID = {$id}";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h3>Classes</h3>";
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
    
    // display the class
	  echo "<tr>";
	  echo "<td><code><a href=\"class.php?id={$row['ID']}\">";
	  echo "{$row['Name']}</a></code></td>";
	  echo "<td>{$row['Description']}</td>";
	  echo "</tr>\n";
  }
  echo "</table>\n";
}


// Show interfaces
$q = "SELECT ID, Name, Description FROM Interfaces WHERE FileID = {$id}";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h3>Interfaces</h3>";
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
    
    // display the class
	  echo "<tr>";
	  echo "<td><code><a href=\"interface.php?id={$row['ID']}\">";
	  echo "{$row['Name']}</a></code></td>";
	  echo "<td>{$row['Description']}</td>";
	  echo "</tr>\n";
  }
  echo "</table>\n";
}


// Show functions
$q = "SELECT ID, Name, Description, Parameters FROM Functions WHERE FileID = {$id} AND ClassID IS NULL AND InterfaceID IS NULL";
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
    }
    $row['Parameters'] = htmlspecialchars($row['Parameters']);
    
    // display the function    
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

