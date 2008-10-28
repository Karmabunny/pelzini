<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of docu.

Docu is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Docu is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with docu.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @package Viewer
**/

require_once 'head.php';


$id = (int) $_GET['id'];
$q = "SELECT Name FROM Packages WHERE ID = {$id} LIMIT 1";
$res = execute_query($q);
if (mysql_num_rows($res) == 0) {
  echo '<p>Invalid package specified.</p>';
}
$row = mysql_fetch_assoc($res);
$row['Name'] = htmlspecialchars($row['Name']);


echo "<h2>{$row['Name']}</h2>";


// Show files
echo "<h3>Files</h3>";
$file_ids = array();

$q = "SELECT Files.ID, Files.Name, Files.Description
  FROM Files
  WHERE Files.PackageID = {$id}";
$res = execute_query ($q);
while ($row = mysql_fetch_assoc ($res)) {
  // encode for output
  $row['Name'] = htmlspecialchars($row['Name']);
  
  // output
  echo "<p><a href=\"file.php?id={$row['ID']}\">{$row['Name']}</a> {$row['Description']}</p>";
  
  $file_ids[] = $row['ID'];
}

$file_ids = implode (', ', $file_ids);


// Show classes
$q = "SELECT ID, Name, Description
  FROM Classes
  WHERE FileID IN ({$file_ids})
  ORDER BY Name";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h3>Classes</h3>";
  echo "<table class=\"function-list\">\n";
  echo "<tr><th>Name</th><th>Description</th></tr>\n";
  while ($row = mysql_fetch_assoc ($res)) {
    // encode for output
    $row['Name'] = htmlspecialchars($row['Name']);
    if ($row['Description'] == null) $row['Description'] = '&nbsp;';
    
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
$q = "SELECT ID, Name, Description
  FROM Interfaces
  WHERE FileID IN ({$file_ids})
  ORDER BY Name";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h3>Interfaces</h3>";
  echo "<table class=\"function-list\">\n";
  echo "<tr><th>Name</th><th>Description</th></tr>\n";
  while ($row = mysql_fetch_assoc ($res)) {
    // encode for output
    $row['Name'] = htmlspecialchars($row['Name']);
    if ($row['Description'] == null) $row['Description'] = '&nbsp;';
    
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
$q = "SELECT ID, Name, Description, Parameters
  FROM Functions
  WHERE FileID IN ({$file_ids}) AND ClassID IS NULL AND InterfaceID IS NULL
  ORDER BY Name";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h3>Functions</h3>";
  echo "<table class=\"function-list\">\n";
  echo "<tr><th>Name</th><th>Description</th></tr>\n";
  while ($row = mysql_fetch_assoc ($res)) {
    // encode for output
    $row['Name'] = htmlspecialchars($row['Name']);
    if ($row['Description'] == null) $row['Description'] = '&nbsp;';
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
