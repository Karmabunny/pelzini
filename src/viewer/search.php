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
* @author Josh Heidenreich
* @since 0.1
**/

require_once 'head.php';

$query = db_escape ($_GET['q']);
$results = false;

echo "<h2>Search</h2>";
echo '<p>You searched for "<strong>', htmlspecialchars($_GET['q']), '</strong>".</p>';


// classes
$q = "SELECT Classes.ID, Classes.Name, Classes.Description, Classes.Extends, Classes.Abstract, Files.Name AS Filename, Classes.FileID
  FROM Classes
  INNER JOIN Files ON Classes.FileID = Files.ID
  WHERE Classes.Name LIKE '%{$query}%' ORDER BY Classes.Name";
$res = db_query ($q);
$num = db_num_rows ($res);
if ($num != 0) {
  $results = true;
  echo '<h3>Classes (', $num, ' result', ($num == 1 ? '' : 's'), ')</h3>';
  
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars ($row['name']);
    $row['filename'] = htmlspecialchars ($row['filename']);
    
    echo "<p><strong><a href=\"class.php?id={$row['id']}\">{$row['name']}</a></strong>";
    
    if ($row['extends'] != null) {
      $row['extends'] = htmlspecialchars($row['extends']);
      echo " <small>extends <a href=\"class.php?name={$row['extends']}\">{$row['extends']}</a></small>";
    }
    
    if ($row['abstract'] == 1) {
      echo " <small>(abstract)</small>";
    }
    
    echo "<br>{$row['description']}";
    echo "<br><small>From <a href=\"file.php?id={$row['fileid']}\">{$row['filename']}</a></small></p>";
  }
}


// functions
$q = "SELECT Functions.ID, Functions.Name, Functions.Description, Functions.ClassID, Files.Name AS Filename, Functions.FileID, Classes.Name AS Class
  FROM Functions
  INNER JOIN Files ON Functions.FileID = Files.ID
  LEFT JOIN Classes ON Functions.ClassID = Classes.ID
  WHERE Functions.Name LIKE '%{$query}%' ORDER BY Functions.Name";
$res = db_query ($q);
$num = db_num_rows ($res);
if ($num != 0) {
  $results = true;
  echo '<h3>Functions (', $num, ' result', ($num == 1 ? '' : 's'), ')</h3>';
  
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars ($row['name']);
    $row['filename'] = htmlspecialchars ($row['filename']);
    
    echo "<p><strong><a href=\"function.php?id={$row['id']}\">{$row['name']}</a></strong>";
    
    if ($row['class'] != null) {
      $row['class'] = htmlspecialchars($row['class']);
      echo " <small>from class <a href=\"class.php?id={$row['classid']}\">{$row['class']}</a></small>";
    }
    
    echo "<br>{$row['description']}";
    echo "<br><small>From <a href=\"file.php?id={$row['fileid']}\">{$row['filename']}</a></small></p>";
  }
}

// no results
if (! $results) {
  echo "<p>Nothing was found!</p>";
}


require_once 'foot.php';
?>
