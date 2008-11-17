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


$id = (int) $_GET['id'];
$q = "SELECT name FROM packages WHERE id = {$id} LIMIT 1";
$res = db_query($q);
if (db_num_rows($res) == 0) {
  echo '<p>Invalid package specified.</p>';
}
$row = db_fetch_assoc($res);
$row['name'] = htmlspecialchars($row['name']);


echo "<h2>{$row['name']}</h2>";


// Show files
echo "<h3>Files</h3>";
$file_ids = array();

$alt = false;
echo '<div class="list">';

$q = "SELECT files.id, files.name, files.description
  FROM files
  WHERE files.packageid = {$id}";
$res = db_query ($q);
while ($row = db_fetch_assoc ($res)) {
  $row['name'] = htmlspecialchars($row['name']);
  
  $class = 'item';
  if ($alt) $class .= '-alt';
  
  // output
  echo "<div class=\"{$class}\">";
  echo "<p><strong><a href=\"file.php?id={$row['id']}\">{$row['name']}</a></strong></p>";
  echo $row['description'];
  echo '</div>';
  
  $file_ids[] = $row['id'];
  $alt = ! $alt;
}
echo '</div>';
$file_ids = implode (', ', $file_ids);


// Show classes
$q = "SELECT id, name, description
  FROM classes
  WHERE fileid IN ({$file_ids})
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo '<a name="classes"></a>';
  echo "<h3>Classes</h3>";
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars($row['name']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    echo "<div class=\"{$class}\">";
    echo "<p><strong><a href=\"class.php?id={$row['id']}\">{$row['name']}</a></strong></p>";
    echo $row['description'];
    echo '</div>';
    
    $alt = ! $alt;
  }
  echo '</div>';
}


// Show interfaces
$q = "SELECT id, name, description
  FROM interfaces
  WHERE fileid IN ({$file_ids})
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo '<a name="interfaces"></a>';
  echo "<h3>Interfaces</h3>";
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    $row['name'] = htmlspecialchars($row['name']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    echo "<div class=\"{$class}\">";
    echo "<p><strong><a href=\"interface.php?id={$row['id']}\">{$row['name']}</a></strong></p>";
    echo $row['description'];
    echo '</div>';
    
    $alt = ! $alt;
  }
  echo '</div>';
}


// Show functions
$q = "SELECT id, name, description, arguments
  FROM functions
  WHERE fileid IN ({$file_ids}) AND classid IS NULL AND interfaceid IS NULL
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo '<a name="functions"></a>';
  echo "<h3>Functions</h3>";
  
  $alt = false;
  echo '<div class="list">';
  while ($row = db_fetch_assoc ($res)) {
    // encode for output
    $row['name'] = htmlspecialchars($row['name']);
    $row['arguments'] = htmlspecialchars($row['arguments']);
    
    $class = 'item';
    if ($alt) $class .= '-alt';
    
    // display the function
    echo "<div class=\"{$class}\">";
    echo "<p><strong><a href=\"function.php?id={$row['id']}\">{$row['name']}</a></strong></p>";
    echo $row['description'];
    echo "</div>";
    
    $alt = ! $alt;
  }
  echo '</div>';
}


// Show constants
$q = "SELECT name, value, description
  FROM constants
  WHERE fileid IN ({$file_ids})
  ORDER BY name";
$res = db_query($q);
if (db_num_rows($res) > 0) {
  echo '<a name="constants"></a>';
  echo "<h3>Constants</h3>";
  
  echo "<table class=\"function-list\">\n";
  echo "<tr><th>Name</th><th>Value</th><th>Description</th></tr>\n";
  while ($row = db_fetch_assoc ($res)) {
    // encode for output
    $row['name'] = htmlspecialchars($row['name']);
    $row['value'] = htmlspecialchars($row['value']);
    if ($row['description'] == null) $row['description'] = '&nbsp;';
    
    // display the constant
    echo "<tr>";
    echo "<td><code>{$row['name']}</code></td>";
    echo "<td><code>{$row['value']}</code></td>";
    echo "<td>{$row['description']}</td>";
    echo "</tr>\n";
  }
  echo "</table>\n";
}

require_once 'foot.php';
?>
