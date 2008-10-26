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
$q = "SELECT Functions.ID, Functions.Name, Functions.Description, Files.Name AS Filename, Functions.ClassID,
  Classes.Name AS Class, Functions.Static, Functions.Final
  FROM Functions
  INNER JOIN Files ON Functions.FileID = Files.ID
  LEFT JOIN Classes ON Functions.ClassID = Classes.ID
  WHERE {$where} LIMIT 1";
$res = execute_query ($q);
$function = mysql_fetch_assoc ($res);

echo "<h2>{$function['Name']}</h2>";

$filename_url = 'file.php?name=' . urlencode($function['Filename']);
echo '<p>File: <a href="', htmlspecialchars($filename_url), '">';
echo htmlspecialchars($function['Filename']), '</a></p>';

if ($function['ClassID'] != null) {
  echo "<p>Class: <a href=\"class.php?id={$function['ClassID']}\">{$function['Class']}</a>";
  
  if ($function['Static']) echo ', Static';
  if ($function['Final']) echo ', Final';
  
  echo '</p>';
}

echo $function['Description'];


// Show parameters
$q = "SELECT ID, Name, Type, Description FROM Parameters WHERE FunctionID = {$function['ID']}";
$res = execute_query($q);
if (mysql_num_rows($res) > 0) {
  echo "<h3>Parameters</h3>";
  echo "<table class=\"parameter-list\">\n";
  echo "<tr><th>Name</th><th>Description</th></tr>\n";
  while ($row = mysql_fetch_assoc ($res)) {
    $row['Name'] = htmlspecialchars($row['Name']);
    if ($row['Description'] == null) $row['Description'] = '&nbsp;';
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

