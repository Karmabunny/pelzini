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
    fatal ("<p>Invalid interface name!</p>");
  }
  $name = mysql_escape ($name);
  $where = "Interfaces.Name LIKE '{$name}'";
} else {
  $where = "Interfaces.ID = {$id}";
}


// Get the details of this class
$q = "SELECT Interfaces.ID, Interfaces.Name, Interfaces.Description, Files.Name AS Filename,
  Interfaces.SinceVersion
  FROM Interfaces
  INNER JOIN Files ON Interfaces.FileID = Files.ID
  WHERE {$where} LIMIT 1";
$res = execute_query ($q);
$row = mysql_fetch_assoc ($res);
echo "<h2>{$row['Name']}</h2>";
$filename_clean = htmlentities(urlencode($row['Filename']));
echo "<p>File: <a href=\"file.php?name={$filename_clean}\">" . htmlentities($row['Filename']) . "</a></p>\n";
echo $row['Description'];
$id = $row['ID'];


show_authors ($row['ID'], LINK_TYPE_INTERFACE);
if ($row['SinceVersion']) echo '<p>Available since: ', htmlspecialchars ($row['SinceVersion']), '</p>';


// Show functions
$q = "SELECT ID, Name, Description, Arguments FROM Functions WHERE InterfaceID = {$id}";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h3>Functions</h3>";
  echo "<table class=\"function-list\">\n";
  echo "<tr><th>Name</th><th>Description</th></tr>\n";
  while ($row = mysql_fetch_assoc ($res)) {
    // encode for output
    $row['Name'] = htmlspecialchars($row['Name']);
    if ($row['Description'] == null) $row['Description'] = '&nbsp;';
    $row['Arguments'] = htmlspecialchars($row['Arguments']);
      
    // display
    echo "<tr>";
    echo "<td><code><a href=\"function.php?id={$row['ID']}\">";
    echo "{$row['Name']}({$row['Arguments']})</a></code></td>";
    echo "<td>{$row['Description']}</td>";
    echo "</tr>\n";
  }
  echo "</table>\n";
}

require_once 'foot.php';
?>

