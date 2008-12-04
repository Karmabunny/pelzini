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


echo "<img src=\"images/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
echo "<span style=\"float: right;\">Show/hide all: &nbsp;</span>";

echo "<h2>Search</h2>";
echo '<p>You searched for "<strong>', htmlspecialchars($_GET['q']), '</strong>".</p>';


// classes
$q = "SELECT classes.id, classes.name, classes.description, classes.extends, classes.abstract, files.name as filename, classes.fileid
  FROM classes
  INNER JOIN files ON classes.fileid = files.id
  WHERE classes.name LIKE '%{$query}%' ORDER BY classes.name";
$res = db_query ($q);
$num = db_num_rows ($res);
if ($num != 0) {
  $results = true;
  echo '<h3>Classes (', $num, ' result', ($num == 1 ? '' : 's'), ')</h3>';
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars ($row['name']);
    $row['filename'] = htmlspecialchars ($row['filename']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    echo "<div class=\"{$class}\">";
    echo "<img src=\"images/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
    echo "<p><strong><a href=\"class.php?id={$row['id']}\">{$row['name']}</a></strong>";
    
    if ($row['extends'] != null) {
      $row['extends'] = htmlspecialchars($row['extends']);
      echo " <small>extends <a href=\"class.php?name={$row['extends']}\">{$row['extends']}</a></small>";
    }
    
    if ($row['abstract'] == 1) {
      echo " <small>(abstract)</small>";
    }
    
    echo "<div class=\"content\">";
    echo $row['description'];
    echo "<br><small>From <a href=\"file.php?id={$row['fileid']}\">{$row['filename']}</a></small></div>";
    echo "</div>";
    
    $alt = ! $alt;
  }
  echo '</div>';
}


// functions
$q = "SELECT functions.id, functions.name, functions.description, functions.classid, files.name as filename, functions.fileid, classes.name as class
  FROM functions
  INNER JOIN files ON functions.fileid = files.id
  LEFT JOIN classes ON functions.classid = classes.id
  WHERE functions.name LIKE '%{$query}%' ORDER BY functions.name";
$res = db_query ($q);
$num = db_num_rows ($res);
if ($num != 0) {
  $results = true;
  echo '<h3>Functions (', $num, ' result', ($num == 1 ? '' : 's'), ')</h3>';
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars ($row['name']);
    $row['filename'] = htmlspecialchars ($row['filename']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    echo "<div class=\"{$class}\">";
    echo "<img src=\"images/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
    echo "<p><strong><a href=\"function.php?id={$row['id']}\">{$row['name']}</a></strong>";
    
    if ($row['class'] != null) {
      $row['class'] = htmlspecialchars($row['class']);
      echo " <small>from class <a href=\"class.php?id={$row['classid']}\">{$row['class']}</a></small>";
    }
    
    echo "<div class=\"content\">";
    echo $row['description'];
    echo "<br><small>From <a href=\"file.php?id={$row['fileid']}\">{$row['filename']}</a></small></div>";
    echo "</div>";
    
    $alt = ! $alt;
  }
  echo '</div>';
}


// source
$q = "SELECT files.id, files.name AS filename, files.source  
  FROM files
  WHERE files.source LIKE '%{$query}%' ORDER BY files.name";
$res = db_query ($q);
$num = db_num_rows ($res);
if ($num != 0) {
  $results = true;
  echo '<h3>Files (', $num, ' result', ($num == 1 ? '' : 's'), ')</h3>';
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    $row['filename'] = htmlspecialchars ($row['filename']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    echo "<div class=\"{$class}\">";
    echo "<img src=\"images/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
    echo "<p><strong><a href=\"file.php?id={$row['id']}\">{$row['filename']}</a></strong></p>";
    
    // Finds the lines, and highlights the term
    echo "<p class=\"content\">";
    $lines = explode("\n", $row['source']);
    foreach ($lines as $num => $line) {
      if (stripos($line, $query) !== false) {
        $num++;
        $line = htmlspecialchars($line);
        $query = htmlspecialchars($query);
        $query = str_replace ('/', '\/', $query);
        $line = preg_replace('/(' . $query . ')/i', "<span class=\"highlight\">\$1</span>", $line);
        
        echo "Line {$num}: <code>{$line}</code><br>";
      }
    }
    echo "</p>";
    echo "</div>";
    
    $alt = ! $alt;
  }
  echo '</div>';
}


// no results
if (! $results) {
  echo "<p>Nothing was found!</p>";
}


require_once 'foot.php';
?>
