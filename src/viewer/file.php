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
$q = "SELECT ID, Name, Description, Packages FROM Files WHERE {$where} LIMIT 1";
$res = execute_query ($q);
$row = mysql_fetch_assoc ($res);
echo "<h2>{$row['Name']}</h2>";
echo "<p>{$row['Description']}</p>";
if ($row['Packages'] != null) {
  echo "<p><strong>Package(s):</strong> {$row['Packages']}</p>";
}
$id = $row['ID'];



// Show classes
$q = "SELECT ID, Name, Description FROM Classes WHERE FileID = {$id}";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h2>Classes</h2>";
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

// Show functions
$q = "SELECT ID, Name, Description, Parameters FROM Functions WHERE FileID = {$id} AND ClassID IS NULL";
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

