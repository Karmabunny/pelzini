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


// Determine what to show
$id = (int) $_GET['id'];
if ($id == 0) {
  $name = trim($_GET['name']);
  if ($name == '') {
    fatal ("<p>Invalid filename!</p>");
  }
  $name = mysql_escape ($name);
  $where = "Files.Name LIKE '{$name}'";
} else {
  $where = "Files.ID = {$id}";
}


// Get the details of this file
$q = "SELECT Files.ID, Files.Name, Files.Description, Files.PackageID, Packages.Name AS Package
  FROM Files
  INNER JOIN Packages ON Files.PackageID = Packages.ID
  WHERE {$where} LIMIT 1";
$res = execute_query ($q);
$row = mysql_fetch_assoc ($res);
echo "<h2>{$row['Name']}</h2>";

if ($row['PackageID'] != null) {
  echo "<p>Package: <a href=\"package.php?id={$row['PackageID']}\">{$row['Package']}</a></p>";
}

echo $row['Description'];
echo "<p><small><a href=\"file_source.php?id={$row['ID']}\">View Source</a></small></p>";
$id = $row['ID'];


// Show classes
$q = "SELECT ID, Name, Description
  FROM Classes
  WHERE FileID = {$id}
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
  WHERE FileID = {$id}
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
  WHERE FileID = {$id} AND ClassID IS NULL AND InterfaceID IS NULL
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



// Show constants
$q = "SELECT Name, Value, Description
  FROM Constants
  WHERE FileID = {$id}
  ORDER BY Name";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h3>Constants</h3>";
  echo "<table class=\"function-list\">\n";
  echo "<tr><th>Name</th><th>Value</th><th>Description</th></tr>\n";
  while ($row = mysql_fetch_assoc ($res)) {
    // encode for output
    $row['Name'] = htmlspecialchars($row['Name']);
    $row['Value'] = htmlspecialchars($row['Value']);
    if ($row['Description'] == null) $row['Description'] = '&nbsp;';
    
    // display the constant
    echo "<tr>";
    echo "<td><code>{$row['Name']}</code></td>";
    echo "<td><code>{$row['Value']}</code></td>";
    echo "<td>{$row['Description']}</td>";
    echo "</tr>\n";
  }
  echo "</table>\n";
}


require_once 'foot.php';
?>
